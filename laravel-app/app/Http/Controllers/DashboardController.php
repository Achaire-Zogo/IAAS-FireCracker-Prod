<?php

namespace App\Http\Controllers;

use App\Models\VirtualMachine;
use App\Models\VmOffer;
use App\Models\SystemImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $virtualMachines = $this->virtualMachines();
        $offers = VmOffer::where('is_active', true)->get();

        // Calcul des statistiques détaillées
        $vmStats = [
            'total' => $virtualMachines->count(),
            'running' => $virtualMachines->where('status', 'running')->count(),
            'stopped' => $virtualMachines->whereIn('status', ['stopped', 'created'])->count(),
            'total_cost' => $virtualMachines->sum('total_cost'),
            'total_cpu' => $virtualMachines->sum('vcpu_count'),
            'total_memory' => $virtualMachines->sum('memory_size_mib'),
            'total_disk' => $virtualMachines->sum('disk_size_gb')
        ];

        return view('dashboard.index', compact('vmStats', 'virtualMachines', 'offers'));
    }

    private function virtualMachines()
    {
        return VirtualMachine::with(['vmOffer', 'systemImage'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Display the specified resource.
     */
    public function show(VirtualMachine $virtualMachine)
    {
        // Vérifier que la VM appartient à l'utilisateur
        if ($virtualMachine->user_id !== Auth::user()->id) {
            abort(403);
        }

        return view('dashboard.show', compact('virtualMachine'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('virtual-machines.create');
    }
}
