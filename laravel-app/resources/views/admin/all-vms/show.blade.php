@extends('layouts.admin')

@section('title', 'Détails de la Machine Virtuelle')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <!-- Informations principales -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informations de la VM</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom</label>
                            <p class="form-control-plaintext">{{ $vm->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-{{ 
                                    $vm->status === 'running' ? 'success' : 
                                    ($vm->status === 'stopped' ? 'danger' : 
                                    ($vm->status === 'error' ? 'warning' : 'info')) 
                                }}">
                                    {{ $vm->status }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Propriétaire</label>
                            <p class="form-control-plaintext">
                                {{ $vm->user->name }}
                                <small class="text-muted d-block">{{ $vm->user->email }}</small>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Créée le</label>
                            <p class="form-control-plaintext">{{ $vm->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuration -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Configuration</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">vCPUs</label>
                            <p class="form-control-plaintext">{{ $vm->vcpu_count }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">RAM</label>
                            <p class="form-control-plaintext">{{ $vm->memory_size_mib }} MiB</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Disque</label>
                            <p class="form-control-plaintext">{{ $vm->disk_size_gb }} GB</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Image Système</label>
                            <p class="form-control-plaintext">{{ $vm->systemImage->name }}</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Offre</label>
                            <p class="form-control-plaintext">{{ $vm->vmOffer->name }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Réseau -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Configuration Réseau</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Adresse IP</label>
                            <p class="form-control-plaintext">{{ $vm->ip_address }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Adresse MAC</label>
                            <p class="form-control-plaintext">{{ $vm->mac_address }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Interface TAP</label>
                            <p class="form-control-plaintext">{{ $vm->tap_device_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">IP TAP</label>
                            <p class="form-control-plaintext">{{ $vm->tap_ip }}</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Namespace Réseau</label>
                            <p class="form-control-plaintext">{{ $vm->network_namespace }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historique -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Historique</h5>
                </div>
                <div class="card-body">
                    @if($vm->historics->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($vm->historics->sortByDesc('created_at') as $historic)
                                        <tr>
                                            <td>{{ $historic->created_at->format('d/m/Y H:i:s') }}</td>
                                            <td>
                                                <span class="badge bg-{{ 
                                                    $historic->status === 'running' ? 'success' : 
                                                    ($historic->status === 'stopped' ? 'danger' : 
                                                    ($historic->status === 'error' ? 'warning' : 'info')) 
                                                }}">
                                                    {{ $historic->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Aucun historique disponible</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($vm->status === 'stopped')
                            <form action="{{ route('virtual-machines.start', $vm) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-play-fill me-2"></i>Démarrer
                                </button>
                            </form>
                        @elseif($vm->status === 'running')
                            <form action="{{ route('virtual-machines.stop', $vm) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="bi bi-stop-fill me-2"></i>Arrêter
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('admin.all-vms.destroy', $vm) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100"
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette VM ?')">
                                <i class="bi bi-trash me-2"></i>Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Utilisation -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Utilisation</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label d-flex justify-content-between">
                            <span>CPU</span>
                            <span>{{ $vm->cpu_usage_percent }}%</span>
                        </label>
                        <div class="progress">
                            <div class="progress-bar" 
                                 role="progressbar" 
                                 style="width: {{ $vm->cpu_usage_percent }}%"
                                 aria-valuenow="{{ $vm->cpu_usage_percent }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-flex justify-content-between">
                            <span>Mémoire</span>
                            <span>{{ $vm->memory_usage_mib }}/{{ $vm->memory_size_mib }} MiB</span>
                        </label>
                        <div class="progress">
                            @php
                                $memoryUsagePercent = ($vm->memory_usage_mib / $vm->memory_size_mib) * 100;
                            @endphp
                            <div class="progress-bar" 
                                 role="progressbar" 
                                 style="width: {{ $memoryUsagePercent }}%"
                                 aria-valuenow="{{ $memoryUsagePercent }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="form-label d-flex justify-content-between">
                            <span>Disque</span>
                            <span>{{ formatBytes($vm->disk_usage_bytes) }}/{{ $vm->disk_size_gb }} GB</span>
                        </label>
                        <div class="progress">
                            @php
                                $diskUsagePercent = ($vm->disk_usage_bytes / ($vm->disk_size_gb * 1024 * 1024 * 1024)) * 100;
                            @endphp
                            <div class="progress-bar" 
                                 role="progressbar" 
                                 style="width: {{ $diskUsagePercent }}%"
                                 aria-valuenow="{{ $diskUsagePercent }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations Système -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations Système</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">PID</label>
                        <p class="form-control-plaintext">{{ $vm->pid ?: 'Non démarré' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Socket Path</label>
                        <p class="form-control-plaintext">{{ $vm->socket_path }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Log Path</label>
                        <p class="form-control-plaintext">{{ $vm->log_path }}</p>
                    </div>
                    <div>
                        <label class="form-label">PID File Path</label>
                        <p class="form-control-plaintext">{{ $vm->pid_file_path }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@php
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    return round($bytes / (1024 ** $pow), $precision) . ' ' . $units[$pow];
}
@endphp
