<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VirtualMachine;
use App\Models\VmOffer;
use App\Models\SystemImage;
use App\Models\Historic;
use App\Models\SshKey;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistiques générales
        $stats = [
            'total_users' => User::count(),
            'total_vms' => VirtualMachine::count(),
            'total_offers' => VmOffer::count(),
            'total_images' => SystemImage::count(),
        ];

        // VMs par statut
        $vmsByStatus = VirtualMachine::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Utilisateurs récents
        $recentUsers = User::latest()
            ->take(5)
            ->get();

        // VMs récentes
        $recentVMs = VirtualMachine::with('user')
            ->latest()
            ->take(5)
            ->get();

        // Activité récente
        $recentActivity = Historic::with(['virtualMachine.user'])
            ->latest()
            ->take(10)
            ->get();

        // Statistiques d'utilisation
        $usageStats = [
            'total_cpu' => VirtualMachine::where('status', 'running')->sum('vcpu_count'),
            'total_memory' => VirtualMachine::where('status', 'running')->sum('memory_size_mib'),
            'total_disk' => VirtualMachine::sum('disk_size_gb'),
            'total_ssh_keys' => SshKey::count(),
        ];

        return view('admin.dashboard', compact(
            'stats',
            'vmsByStatus',
            'recentUsers',
            'recentVMs',
            'recentActivity',
            'usageStats'
        ));
    }
}
