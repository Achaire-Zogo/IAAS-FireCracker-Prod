<?php

namespace App\Http\Controllers;

use RuntimeException;
use App\Models\SshKey;
use GuzzleHttp\Client;
use App\Models\VmOffer;
use App\Models\SystemImage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\VirtualMachine;
use App\Services\ContainerdService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Process;

class VirtualMachineController extends Controller
{
    private $containerdService;

    public function __construct(ContainerdService $containerdService)
    {
        $this->containerdService = $containerdService;
    }

    private function generateSshKeyPair()
    {
        $keyName = 'vm_' . Str::random(10).'_'.time();
        $privateKeyPath = public_path("ssh_keys_vm/{$keyName}");
        $publicKeyPath = "{$privateKeyPath}.pub";

        // Créer le répertoire s'il n'existe pas
        if (!file_exists(dirname($privateKeyPath))) {
            mkdir(dirname($privateKeyPath), 0755, true);
        }

        // Générer la paire de clés SSH
        $result = Process::run("ssh-keygen -t rsa -b 4096 -f {$privateKeyPath} -N ''");

        if (!$result->successful()) {
            throw new RuntimeException('Failed to generate SSH key pair: ' . $result->errorOutput());
        }

        return [
            'private_key' => file_get_contents($privateKeyPath),
            'public_key' => file_get_contents($publicKeyPath),
            'key_name' => $keyName
        ];
    }

    private function generateMacAddress()
    {
        // Le format de base pour l'adresse MAC : 06:00:AC:10:00:XX
        // Où XX est un nombre hexadécimal entre 02 et FF (2-255 en décimal)
        // Cela donnera des adresses IP 172.16.0.2 à 172.16.0.255

        $lastByte = sprintf('%02X', rand(2, 255)); // Génère un nombre entre 02 et FF en hex
        return "06:00:AC:10:00:{$lastByte}";
    }

    private function generateIpFromMac($macAddress)
    {
        // Extraire le dernier octet de l'adresse MAC (en hex)
        $lastByte = substr($macAddress, -2);
        // Convertir en décimal
        $lastOctet = hexdec($lastByte);
        // Construire l'adresse IP
        return "172.16.0.{$lastOctet}";
    }

    public function index()
    {
        $virtualMachines = Auth::user()->virtualMachines;
        return view('virtual-machines.index', compact('virtualMachines'));
    }

    public function create()
    {
        $offers = VmOffer::where('is_active', true)->get();
        $systemImages = SystemImage::all();

        // Récupérer l'offre présélectionnée si elle existe
        $selectedOfferId = request('offer');
        $selectedOffer = $selectedOfferId ? VmOffer::find($selectedOfferId) : null;

        return view('virtual-machines.create', compact('offers', 'systemImages', 'selectedOffer'));
    }

    public function store(Request $request)
    {
        try{
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
                'vm_offer_id' => ['required', 'exists:vm_offers,id'],
                'system_image_id' => ['required', 'exists:system_images,id']
            ]);

            // Récupérer l'offre et l'image système
            $offer = VmOffer::findOrFail($validated['vm_offer_id']);
            $systemImage = SystemImage::findOrFail($validated['system_image_id']);

            // Créer la VM avec les paramètres de base
            $vm = new VirtualMachine();
            $vm->name = $validated['name'];
            $vm->user_id = Auth::user()->id;
            $vm->vm_offer_id = $offer->id;
            $vm->system_image_id = $systemImage->id;
            $vm->root_password_hash = $validated['password'];

            // Copier les caractéristiques de l'offre
            $vm->vcpu_count = $offer->cpu_count;
            $vm->memory_size_mib = $offer->memory_size_mib;
            $vm->disk_size_gb = $offer->disk_size_gb;

            // Configurer le réseau
            $vm->mac_address = $this->generateMacAddress();
            $vm->ip_address = $this->generateIpFromMac($vm->mac_address);
            $vm->tap_device_name = 'tap0';
            $vm->tap_ip = '172.16.0.1'; // IP fixe pour l'interface TAP
            $vm->network_namespace = 'ns_' . Str::slug($vm->name);

            // Configurer SSH
            $vm->ssh_port = 22;

            // Paramètres par défaut
            $vm->track_dirty_pages = true;
            $vm->allow_mmds_requests = false;
            $vm->status = 'creating';

            // Créer une clé SSH si elle n'existe pas
            if (!$vm->sshKey) {
                $sshKeyPair = $this->generateSshKeyPair();
                $vm->sshKey()->create([
                    'user_id'=> Auth::user()->id,
                    'name' => $sshKeyPair['key_name'],
                    'public_key' => $sshKeyPair['public_key'],
                    'private_key' => $sshKeyPair['private_key']
                ]);
            }



            try {
                // Préparer les données pour l'API Python selon VMConfig
                $vmData = [
                    'name' => $vm->name,
                    'user_id' => (string) Auth::id(), // L'API attend un string
                    'cpu_count' => $vm->vcpu_count,
                    'memory_size_mib' => $vm->memory_size_mib,
                    'disk_size_gb' => $vm->disk_size_gb,
                    'os_type' => $systemImage->name, // Le type d'OS est le nom de l'image
                    'ssh_public_key' => $vm->sshKey->public_key,
                    'root_password' => $validated['password'],
                    'tap_device' => $vm->tap_device_name,
                    'tap_ip' => $vm->tap_ip,
                    'vm_ip' => $vm->ip_address
                ];

                // Envoyer la requête à l'API Python
                $client = new Client();
                $response = $client->post(config('services.firecracker.api_url') . '/vm/create', [
                    'json' => $vmData,
                    'timeout' => config('services.firecracker.timeout')
                ]);

                $result = json_decode($response->getBody(), true);

                if ($result['success']) {
                    // La VM a été créée avec succès
                    $vm->status = 'created';
                    $vm->last_error = null;

                    // Stocker les données supplémentaires si présentes
                    if (isset($result['data'])) {
                        if (isset($result['data']['socket_path'])) {
                            $vm->socket_path = $result['data']['socket_path'];
                        }
                        if (isset($result['data']['vm_path'])) {
                            $vm->vm_path = $result['data']['vm_path'];
                        }
                    }

                    Log::info('VM created successfully', [
                        'vm_id' => $vm->id,
                        'name' => $vm->name,
                        'response' => $result
                    ]);

                    // Sauvegarder la VM
                    $vm->save();
                    return redirect()->route('virtual-machines.show', $vm->id)
                        ->with('success', 'Virtual machine is being created. Please wait while we set everything up.');
                } else {
                    // Erreur lors de la création
                    $vm->status = 'failed';
                    $vm->last_error = $result['message'] ?? 'Unknown error during VM creation';
                    Log::error('VM creation failed', [
                        'vm_id' => $vm->id,
                        'error' => $vm->last_error,
                        'response' => $result
                    ]);
                }
            } catch (\Exception $e) {
                // Erreur de communication avec l'API
                $vm->status = 'failed';
                $vm->last_error = 'Failed to communicate with Firecracker API: ' . $e->getMessage();
                Log::error('VM creation API error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }



            return redirect()->route('virtual-machines.create')
                ->with('error', 'An error occurred while creating the virtual machine.');
        }catch(\Exception $e){
            Log::info('An error occured', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('dashboard.index')
                ->with('error', 'Erreur lors de la création de la machine virtuelle : ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        // Récupérer la VM avec ses relations
        $vm = VirtualMachine::with(['vmOffer', 'systemImage', 'sshKey'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        // Calculer le coût total depuis la création
        $hoursRunning = $vm->created_at->diffInHours(now());
        $totalCost = $hoursRunning * $vm->vmOffer->price_per_hour;

        // Récupérer les métriques système si disponibles
        $metrics = [
            'cpu_usage' => rand(0, 100), // À remplacer par les vraies métriques
            'memory_usage' => rand(0, $vm->memory_size_mib),
            'disk_usage' => rand(0, $vm->disk_size_gb * 1024), // Convertir en MB
            'network_rx' => rand(0, 1000), // MB reçus
            'network_tx' => rand(0, 1000)  // MB transmis
        ];

        // Récupérer l'historique des statuts (à implémenter avec un modèle VmStatusHistory)
        $statusHistory = [
            ['status' => 'creating', 'timestamp' => $vm->created_at],
            ['status' => 'running', 'timestamp' => $vm->created_at->addMinutes(2)],
            // Ajouter d'autres statuts selon l'historique
        ];

        // Préparer les informations de connexion SSH
        $sshInfo = [
            'host' => $vm->ip_address,
            'port' => $vm->ssh_port,
            'username' => 'root',
            'key_path' => $vm->sshKey ? public_path("ssh_keys_vm/{$vm->sshKey->name}") : null
        ];

        return view('virtual-machines.show', compact(
            'vm',
            'totalCost',
            'metrics',
            'statusHistory',
            'sshInfo'
        ));
    }

    public function start($id)
    {
        $vm = VirtualMachine::findOrFail($id);

        try {
            $this->containerdService->startVM($vm->name);
            $vm->update(['status' => 'running']);

            return redirect()->route('dashboard')
                ->with('success', 'Machine virtuelle démarrée avec succès.');
        } catch (RuntimeException $e) {
            return redirect()->route('dashboard')
                ->with('error', 'Échec du démarrage de la machine virtuelle : ' . $e->getMessage());
        }
    }

    public function stop($id)
    {
        $vm = VirtualMachine::findOrFail($id);

        try {
            $this->containerdService->stopVM($vm->name);
            $vm->update(['status' => 'stopped']);

            return redirect()->route('dashboard')
                ->with('success', 'Machine virtuelle arrêtée avec succès.');
        } catch (RuntimeException $e) {
            return redirect()->route('dashboard')
                ->with('error', 'Échec de l\'arrêt de la machine virtuelle : ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $vm = VirtualMachine::findOrFail($id);

        try {
            // Arrêter la VM si elle est en cours d'exécution
            if ($vm->status === 'running') {
                $this->containerdService->stopVM($vm->name);
            }

            // Supprimer la VM de Containerd
            $this->containerdService->deleteVM($vm->name);

            // Supprimer la clé SSH associée
            if ($vm->sshKey) {
                $vm->sshKey->delete();
            }

            // Supprimer la VM de la base de données
            $vm->delete();

            return redirect()->route('dashboard')
                ->with('success', 'Machine virtuelle supprimée avec succès.');
        } catch (RuntimeException $e) {
            return redirect()->route('dashboard')
                ->with('error', 'Échec de la suppression de la machine virtuelle : ' . $e->getMessage());
        }
    }

    public function getSSHDetails($id)
    {
        $vm = VirtualMachine::findOrFail($id);

        if (!$vm->sshKey) {
            return response()->json([
                'error' => 'Aucune clé SSH associée à cette machine virtuelle'
            ], 404);
        }

        $status = $this->containerdService->getVMStatus($vm->name);

        if ($status['status'] !== 'running') {
            return response()->json([
                'error' => 'La machine virtuelle n\'est pas en cours d\'exécution'
            ], 400);
        }

        return response()->json([
            'host' => 'localhost', // À remplacer par l'IP réelle de la VM
            'port' => 22,
            'username' => 'root',
            'private_key' => $vm->sshKey->private_key
        ]);
    }

    private function getRootfsPath(string $osType): string
    {
        $basePath = config('services.containerd.rootfs_path');

        return match($osType) {
            'ubuntu' => "{$basePath}/ubuntu-22.04.ext4",
            'debian' => "{$basePath}/debian-11.ext4",
            'centos' => "{$basePath}/centos-9.ext4",
            default => throw new RuntimeException("Type d'OS non supporté : {$osType}")
        };
    }
}
