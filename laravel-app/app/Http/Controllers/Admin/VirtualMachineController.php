<?php

namespace App\Http\Controllers\Admin;

use App\Models\VirtualMachine;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class VirtualMachineController extends Controller
{
    public function index(Request $request)
    {
        $query = VirtualMachine::with(['user', 'vmOffer', 'systemImage']);

        // Filtrage par utilisateur
        if ($request->has('user')) {
            $query->where('user_id', $request->user);
        }

        // Filtrage par statut
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Recherche
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $virtualMachines = $query->orderBy('created_at', 'desc')->paginate(10);
        $users = User::all();

        // Statistiques globales
        $stats = [
            'running' => VirtualMachine::where('status', 'running')->count(),
            'total_cpu' => VirtualMachine::sum('vcpu_count'),
            'total_memory' => VirtualMachine::sum('memory_size_mib'),
            'total_disk' => VirtualMachine::sum('disk_size_gb')
        ];

        return view('admin.all-vms.index', compact('virtualMachines', 'users', 'stats'));
    }

    public function show(VirtualMachine $virtualMachine)
    {
        $virtualMachine->load(['user', 'vmOffer', 'systemImage', 'sshKey', 'historics']);
        return view('admin.all-vms.show', ['vm' => $virtualMachine]);
    }

    public function destroy(VirtualMachine $virtualMachine)
    {
        try {
            // Arrêt de la VM si elle est en cours d'exécution
            if ($virtualMachine->status === 'running') {
                // Appel à la méthode d'arrêt de la VM
                $virtualMachine->stopVM();
            }

            // Suppression des fichiers associés
            if (Storage::exists($virtualMachine->log_path)) {
                Storage::delete($virtualMachine->log_path);
            }
            if (Storage::exists($virtualMachine->pid_file_path)) {
                Storage::delete($virtualMachine->pid_file_path);
            }
            if (Storage::exists($virtualMachine->socket_path)) {
                Storage::delete($virtualMachine->socket_path);
            }

            // Suppression de la VM de la base de données
            $virtualMachine->delete();

            return redirect()->route('admin.all-vms.index')
                           ->with('success', 'Machine virtuelle supprimée avec succès');
        } catch (\Exception $e) {
            return redirect()->route('admin.all-vms.index')
                           ->with('error', 'Erreur lors de la suppression de la machine virtuelle : ' . $e->getMessage());
        }
    }
}
