<?php

namespace App\Http\Controllers;

use App\Models\Historic;
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

    private function generateMacAddress($ip_address)
    {
        // Extraire les octets de l'adresse IP
        preg_match('/172\.16\.(\d+)\.(\d+)/', $ip_address, $matches);

        // Utiliser les octets de l'IP pour générer une MAC unique
        return sprintf("06:00:AC:10:%02x:%02x",
            $matches[1],  // Troisième octet de l'IP
            $matches[2]   // Quatrième octet de l'IP
        );
    }

    private function generateIpFromSequence($sequence)
    {
        // Pour un sous-réseau /30, A = 4
        $A = 4;

        // Calculer O à partir de l'IP de la VM
        $octet3 = ($A * $sequence + 2) / 256;
        $octet4 = ($A * $sequence + 2) % 256;

        return sprintf("192.168.%d.%d", $octet3, $octet4);
    }

    private function generateTapIpFromSequence($sequence)
    {

        // Pour un sous-réseau /30, A = 4
        $A = 4;

        // Calculer O à partir de l'IP de la VM
        $octet3 = ($A * $sequence + 1) / 256;
        $octet4 = ($A * $sequence + 1) % 256;

        return sprintf("192.168.%d.%d", $octet3, $octet4);
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



            $vm->network_namespace = 'ns_' . Str::slug($vm->name);

            // Configurer SSH
            $vm->ssh_port = 22;

            // Paramètres par défaut
            $vm->track_dirty_pages = true;
            $vm->allow_mmds_requests = false;
            $vm->status = 'creating';

            // Créer une clé SSH si elle n'existe pas
            $ssh = new SshKey();
            if (!$vm->sshKey) {
                $sshKeyPair = $this->generateSshKeyPair();


                $ssh->user_id = Auth::user()->id;
                $ssh->name = $sshKeyPair['key_name'];
                $ssh->public_key = $sshKeyPair['public_key'];
                $ssh->private_key = $sshKeyPair['private_key'];
                $ssh->save();
            }



            try {
                // Préparer les données pour l'API Python selon VMConfig
                $vm->save();
                // Configurer le réseau
                $vm->ssh_key_id = $ssh->id;
                $vm->tap_device_name = 'tap'.$vm->id;
                $vm->ip_address = $this->generateIpFromSequence($vm->id);
                $vm->tap_ip = $this->generateTapIpFromSequence($vm->id);

                $vm->mac_address = $this->generateMacAddress($vm->ip_address);

                $vmData = [
                    'name' => $vm->name,
                    'user_id' => (string) Auth::id(), // L'API attend un string
                    'cpu_count' => $vm->vcpu_count,
                    'memory_size_mib' => $vm->memory_size_mib,
                    'disk_size_gb' => $vm->disk_size_gb,
                    'os_type' => $systemImage->os_type, // Le type d'OS est le nom de l'image
                    'ssh_public_key' => $sshKeyPair['public_key'],
                    'root_password' => $validated['password'],
                    'tap_device' => $vm->tap_device_name,
                    'tap_ip' => $vm->tap_ip,
                    'vm_ip' => $vm->ip_address,
                    'vm_mac' => $vm->mac_address
                ];
                Log::info($vmData);
                // Envoyer la requête à l'API Python
                $client = new Client();
                $response = $client->post(env('API_FIRECRACKER_URL') . '/vm/create', [
                    'json' => $vmData,
                    'timeout' => 5000
                ]);

                $result = json_decode($response->getBody(), true);

                if ($result['success']) {
                    // La VM a été créée avec succès
                    $vm->status = 'created';

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
                    $vm->status = 'stopped';
                    $vm->save();
                    //Historique
                    $hist = new Historic();
                    $hist->vm_id = $vm->id;
                    $hist->status = 'created';
                    $hist->save();
                    return redirect()->route('virtual-machines.show', $vm->id)
                        ->with('success', 'Virtual machine is being created. Please wait while we set everything up.');
                } else {
                    // Erreur lors de la création
                    $vm->status = 'failed';

                    Log::error('VM creation failed', [
                        'vm_id' => $vm->id,
                        'response' => $result
                    ]);
                }
            } catch (\Exception $e) {
                // Erreur de communication avec l'API
                // $vm->status = 'failed';

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
        $statusHistory = Historic::where('vm_id', $vm->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Préparer les informations de connexion SSH
        $sshInfo = [
            'host' => $vm->ip_address,
            'port' => $vm->ssh_port,
            'username' => 'root',
            'private_key' => $vm->sshKey->private_key
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

        // Récupérer l'offre et l'image système
        $offer = VmOffer::findOrFail($vm->vm_offer_id);
        $systemImage = SystemImage::findOrFail($vm->system_image_id);

        try {
            // Préparer les données pour l'API Python selon VMStartConfig
            $vmData = [
                'name' => $vm->name,
                'user_id' => (string) Auth::user()->id, // L'API attend un string
                'cpu_count' => $vm->vcpu_count,
                'os_type' => $systemImage->os_type,
                'memory_size_mib' => $vm->memory_size_mib,
                'disk_size_gb' => $vm->disk_size_gb,
                'tap_device' => $vm->tap_device_name,
                'tap_ip' => $vm->tap_ip,
                'vm_ip' => $vm->ip_address,
                'vm_mac' => $vm->mac_address
            ];

            //dd($vmData);

            // Envoyer la requête à l'API Python
            $client = new Client();
            $response = $client->post(env('API_FIRECRACKER_URL') . '/vm/start', [
                'json' => $vmData,
                'timeout' => 5000 // Timeout plus long pour le démarrage
            ]);

            $result = json_decode($response->getBody(), true);

            if ($result['success']) {
                $vm->status = 'running';
                $vm->save();
                //Historique
                $hist = new Historic();
                $hist->vm_id = $vm->id;
                $hist->status = 'running';
                $hist->save();

                Log::info('VM started successfully', [
                    'vm_id' => $vm->id,
                    'name' => $vm->name,
                    'response' => $result
                ]);

                return redirect()->back()
                    ->with('success', 'Virtual machine started successfully.');
            } else {
                $vm->status = 'error';
                $vm->save();
                //Historique
                $hist = new Historic();
                $hist->vm_id = $vm->id;
                $hist->status = 'error';
                $hist->save();

                Log::error('VM start failed', [
                    'vm_id' => $vm->id,

                    'response' => $result
                ]);

                return redirect()->back()
                    ->with('error', 'Failed to start virtual machine.');
            }
        } catch (\Exception $e) {
            $vm->status = 'error';
            $vm->save();
            //Historique
            $hist = new Historic();
            $hist->vm_id = $vm->id;
            $hist->status = 'error';
            $hist->save();

            Log::error('VM start API error', [
                'vm_id' => $vm->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'An error occurred while starting the virtual machine.');
        }
    }

    public function stop($id)
    {
        $vm = VirtualMachine::findOrFail($id);

        try {
            $client = new Client();
            $response = $client->post(env('API_FIRECRACKER_URL') . '/vm/stop', [
                'json' => [
                    'name' => $vm->name,
                    'user_id' => (string) Auth::id()
                ]
            ]);

            $result = json_decode($response->getBody(), true);
            Log::info('VM stopped successfully', [
                'vm_id' => $vm->id,
                'name' => $vm->name,
                'response' => $result
            ]);

            $vm->status = 'stopped';
            $vm->save();
            //Historique
            $hist = new Historic();
            $hist->vm_id = $vm->id;
            $hist->status = 'stopped';
            $hist->save();

            return redirect()->route('virtual-machines.show', $vm)
                ->with('success', 'Machine virtuelle arrêtée avec succès');

        } catch (\Exception $e) {
            Log::error('Failed to stop VM', [
                'vm_id' => $vm->id,
                'name' => $vm->name,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('virtual-machines.show', $vm)
                ->with('error', 'Erreur lors de l\'arrêt de la machine virtuelle : ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $vm = VirtualMachine::findOrFail($id);

        try {
            // Appeler l'API Python pour supprimer la VM
            $client = new Client();
            $response = $client->post(env('API_FIRECRACKER_URL') . '/vm/delete', [
                'json' => [
                    'name' => $vm->name,
                    'user_id' => (string) Auth::id()
                ]
            ]);

            $result = json_decode($response->getBody(), true);
            Log::info('VM deleted successfully', [
                'vm_id' => $vm->id,
                'name' => $vm->name,
                'response' => $result
            ]);

            // Supprimer la clé SSH associée si elle existe
            if ($vm->sshKey) {
                $vm->sshKey->delete();
            }

            // Supprimer la VM de la base de données
            $vm->delete();


            return redirect()->route('dashboard.index')
                ->with('success', 'Machine virtuelle supprimée avec succès.');
        } catch (\Exception $e) {
            Log::error('Failed to delete VM', [
                'vm_id' => $vm->id,
                'name' => $vm->name,
                'error' => $e->getMessage()
            ]);

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


}
