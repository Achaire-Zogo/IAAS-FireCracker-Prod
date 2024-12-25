@extends('layouts.admin')

@section('title', 'Tableau de Bord Administration')

@section('content')
<div class="container-fluid">
    <!-- Cartes de statistiques -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Utilisateurs</h6>
                            <h2 class="mb-0 mt-2">{{ $stats['total_users'] }}</h2>
                        </div>
                        <i class="bi bi-people fs-1 opacity-50"></i>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="text-white stretched-link"></a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Machines Virtuelles</h6>
                            <h2 class="mb-0 mt-2">{{ $stats['total_vms'] }}</h2>
                        </div>
                        <i class="bi bi-pc-display fs-1 opacity-50"></i>
                    </div>
                    <a href="{{ route('admin.all-vms.index') }}" class="text-white stretched-link"></a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Offres VM</h6>
                            <h2 class="mb-0 mt-2">{{ $stats['total_offers'] }}</h2>
                        </div>
                        <i class="bi bi-box fs-1 opacity-50"></i>
                    </div>
                    <a href="{{ route('admin.vm-offers.index') }}" class="text-white stretched-link"></a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Images Système</h6>
                            <h2 class="mb-0 mt-2">{{ $stats['total_images'] }}</h2>
                        </div>
                        <i class="bi bi-hdd fs-1 opacity-50"></i>
                    </div>
                    <a href="{{ route('admin.system-images.index') }}" class="text-white stretched-link"></a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Statistiques d'utilisation -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statistiques d'Utilisation</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6 class="text-muted mb-2">CPU Total</h6>
                                <h4 class="mb-0">{{ $usageStats['total_cpu'] }} vCPUs</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6 class="text-muted mb-2">Mémoire Totale</h6>
                                <h4 class="mb-0">{{ $usageStats['total_memory'] }} MiB</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6 class="text-muted mb-2">Stockage Total</h6>
                                <h4 class="mb-0">{{ $usageStats['total_disk'] }} GB</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6 class="text-muted mb-2">Clés SSH</h6>
                                <h4 class="mb-0">{{ $usageStats['total_ssh_keys'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- État des VMs -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">État des Machines Virtuelles</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6 class="text-muted mb-2">En cours</h6>
                                <h4 class="mb-0 text-success">{{ $vmsByStatus['running'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6 class="text-muted mb-2">Arrêtées</h6>
                                <h4 class="mb-0 text-danger">{{ $vmsByStatus['stopped'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6 class="text-muted mb-2">En erreur</h6>
                                <h4 class="mb-0 text-warning">{{ $vmsByStatus['error'] ?? 0 }}</h4>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h6 class="text-muted mb-2">En création</h6>
                                <h4 class="mb-0 text-info">{{ $vmsByStatus['creating'] ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Utilisateurs récents -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Utilisateurs Récents</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentUsers as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : 'bg-primary' }}">
                                        {{ $user->role }}
                                    </span>
                                </td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Activité récente -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Activité Récente</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>VM</th>
                                <th>Utilisateur</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentActivity as $activity)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ $activity->status === 'running' ? 'success' : ($activity->status === 'stopped' ? 'danger' : 'info') }}">
                                        {{ $activity->status }}
                                    </span>
                                </td>
                                <td>{{ $activity->virtualMachine->name }}</td>
                                <td>{{ $activity->virtualMachine->user->name }}</td>
                                <td>{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
