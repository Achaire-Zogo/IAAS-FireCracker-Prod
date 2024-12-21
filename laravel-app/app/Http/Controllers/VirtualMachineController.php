<?php

namespace App\Http\Controllers;

use App\Models\VirtualMachine;
use App\Models\SshKey;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'os_type' => 'required|in:ubuntu,debian,centos',
            'vcpu_count' => 'required|integer|min:1|max:8',
            'mem_size_mib' => 'required|integer|min:512|max:16384'
        ]);

        try {
            // Générer une nouvelle paire de clés SSH
            $sshKeys = $this->generateSshKeyPair();

            // Sauvegarder la clé SSH
            $sshKey = new SshKey([
                'name' => $sshKeys['key_name'],
                'private_key' => $sshKeys['private_key'],
                'public_key' => $sshKeys['public_key']
            ]);
            $sshKey->user_id = Auth::id();
            $sshKey->save();

            // Créer la VM dans la base de données
            $vm = new VirtualMachine([
                'name' => $validated['name'],
                'os_type' => $validated['os_type'],
                'vcpu_count' => $validated['vcpu_count'],
                'mem_size_mib' => $validated['mem_size_mib'],
                'status' => 'creating',
                'user_id' => Auth::id(),
                'ssh_key_id' => $sshKey->id
            ]);
            
            $vm->save();

            // Configurer la VM avec Containerd
            $config = [
                'kernel_path' => config('services.containerd.kernel_path'),
                'rootfs' => $this->getRootfsPath($validated['os_type']),
                'vcpu_count' => $validated['vcpu_count'],
                'mem_size_mib' => $validated['mem_size_mib'],
                'ssh_public_key' => $sshKeys['public_key']
            ];

            $result = $this->containerdService->createVM($validated['name'], $config);
            
            // Mettre à jour le statut de la VM
            $vm->update(['status' => 'running']);

            return redirect()->route('dashboard')
                ->with('success', 'Machine virtuelle créée avec succès.');

        } catch (RuntimeException $e) {
            // En cas d'erreur, supprimer la VM et la clé SSH de la base de données
            if (isset($vm)) {
                $vm->delete();
            }
            if (isset($sshKey)) {
                $sshKey->delete();
            }
            
            return redirect()->route('dashboard')
                ->with('error', 'Échec de la création de la machine virtuelle : ' . $e->getMessage());
        }
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
