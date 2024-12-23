<?php

namespace App\Http\Controllers;

use App\Models\VirtualMachine;
use App\Models\SshKey;
use App\Models\VmOffer;
use App\Models\SystemImage;
use App\Services\ContainerdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;

class VirtualMachineController extends Controller
{
    private $containerdService;

    public function __construct(ContainerdService $containerdService)
    {
        $this->containerdService = $containerdService;
    }

    private function generateSshKeyPair()
    {
        $keyName = 'vm_' . Str::random(10);
        $privateKeyPath = storage_path("app/ssh_keys/{$keyName}");
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
        // Valider les données
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vm_offer_id' => 'required|exists:vm_offers,id',
            'system_image_id' => 'required|exists:system_images,id',
            'password' => 'required|string|min:8|max:255'
        ]);

        // Récupérer l'offre et l'image système
        $offer = VmOffer::findOrFail($validated['vm_offer_id']);
        $systemImage = SystemImage::findOrFail($validated['system_image_id']);

        // Créer la VM avec les paramètres de base
        $vm = new VirtualMachine();
        $vm->name = $validated['name'];
        $vm->user_id = Auth::id();
        $vm->vm_offer_id = $offer->id;
        $vm->system_image_id = $systemImage->id;
        $vm->root_password_hash = password_hash($validated['password'], PASSWORD_DEFAULT);

        // Copier les caractéristiques de l'offre
        $vm->vcpu_count = $offer->cpu_count;
        $vm->memory_size_mib = $offer->memory_size_mib;
        $vm->disk_size_gb = $offer->disk_size_gb;

        // Configurer les chemins des fichiers
        $baseDir = storage_path('app/vms/' . Str::slug($vm->name));
        $vm->kernel_image_path = $systemImage->kernel_path;
        $vm->rootfs_path = $baseDir . '/rootfs.ext4';
        $vm->socket_path = $baseDir . '/firecracker.sock';
        $vm->log_path = $baseDir . '/firecracker.log';
        $vm->pid_file_path = $baseDir . '/firecracker.pid';

        // Générer une adresse MAC et IP uniques
        $vm->generateMacAddress();
        $vm->generateIpAddress();

        // Configurer le réseau
        $vm->tap_device_name = 'tap' . Str::random(8);
        $vm->tap_ip = '172.16.0.1'; // IP fixe pour l'interface TAP
        $vm->network_namespace = 'ns_' . Str::slug($vm->name);
        
        // Configurer SSH
        $vm->ssh_port = $this->findAvailablePort(22000, 23000);
        
        // Paramètres par défaut
        $vm->track_dirty_pages = true;
        $vm->allow_mmds_requests = false;
        $vm->balloon_deflate_on_oom = true;
        $vm->status = 'creating';

        // Sauvegarder la VM
        $vm->save();

        // Rediriger vers la page de détails
        return redirect()->route('dashboard.show', $vm->id)
            ->with('success', 'Virtual machine is being created. Please wait while we set everything up.');
    }

    private function findAvailablePort($start, $end)
    {
        $usedPorts = VirtualMachine::whereNotNull('ssh_port')
            ->pluck('ssh_port')
            ->toArray();

        for ($port = $start; $port <= $end; $port++) {
            if (!in_array($port, $usedPorts)) {
                return $port;
            }
        }

        throw new \RuntimeException('No available ports found');
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
