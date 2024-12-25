@extends('layouts.admin')

@section('title', 'Toutes les Machines Virtuelles')

@section('content')
<div class="container-fluid">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h1 class="mb-0 h3">Toutes les Machines Virtuelles</h1>

        <!-- Filtres -->
        <div class="gap-3 d-flex">
            <form action="{{ route('admin.all-vms.index') }}" method="GET" class="gap-2 d-flex">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Tous les statuts</option>
                    <option value="running" {{ request('status') == 'running' ? 'selected' : '' }}>En cours</option>
                    <option value="stopped" {{ request('status') == 'stopped' ? 'selected' : '' }}>Arrêtée</option>
                    <option value="error" {{ request('status') == 'error' ? 'selected' : '' }}>Erreur</option>
                    <option value="creating" {{ request('status') == 'creating' ? 'selected' : '' }}>En création</option>
                </select>

                <select name="user" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Tous les utilisateurs</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                </select>

                <input type="text"
                       name="search"
                       class="form-control form-control-sm"
                       placeholder="Rechercher..."
                       value="{{ request('search') }}">

                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search"></i>
                </button>

                @if(request()->hasAny(['status', 'user', 'search']))
                    <a href="{{ route('admin.all-vms.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($virtualMachines->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Utilisateur</th>
                                <th>Configuration</th>
                                <th>Status</th>
                                <th>Utilisation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($virtualMachines as $vm)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $vm->name }}</span>
                                            <small class="text-muted">ID: {{ $vm->id }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $vm->user->name }}</span>
                                            <small class="text-muted">{{ $vm->user->email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $vm->vcpu_count }} vCPUs, {{ $vm->memory_size_mib }} MiB</span>
                                            <small class="text-muted">{{ $vm->disk_size_gb }} GB</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{
                                            $vm->status === 'running' ? 'success' :
                                            ($vm->status === 'stopped' ? 'danger' :
                                            ($vm->status === 'error' ? 'warning' : 'info'))
                                        }}">
                                            {{ $vm->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <div class="gap-2 d-flex align-items-center">
                                                <i class="bi bi-cpu"></i>
                                                <div class="progress flex-grow-1" style="height: 6px;">
                                                    <div class="progress-bar"
                                                         role="progressbar"
                                                         style="width: {{ $vm->cpu_usage_percent }}%"
                                                         aria-valuenow="{{ $vm->cpu_usage_percent }}"
                                                         aria-valuemin="0"
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <small>{{ $vm->cpu_usage_percent }}%</small>
                                            </div>
                                            <div class="gap-2 d-flex align-items-center">
                                                <i class="bi bi-memory"></i>
                                                <div class="progress flex-grow-1" style="height: 6px;">
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
                                                <small>{{ $vm->memory_usage_mib }}/{{ $vm->memory_size_mib }} MiB</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.all-vms.show', $vm) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="Voir les détails">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if($vm->status === 'stopped')
                                                <form action="{{ route('virtual-machines.start', $vm) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-success"
                                                            title="Démarrer">
                                                        <i class="bi bi-play-fill"></i>
                                                    </button>
                                                </form>
                                            @elseif($vm->status === 'running')
                                                <form action="{{ route('virtual-machines.stop', $vm) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Arrêter">
                                                        <i class="bi bi-stop-fill"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('admin.all-vms.destroy', $vm) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Supprimer"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette VM ?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $virtualMachines->links() }}
                </div>
            @else
                <div class="py-4 text-center">
                    <i class="bi bi-pc-display h1 text-muted"></i>
                    <p class="text-muted">Aucune machine virtuelle trouvée</p>
                </div>
            @endif
        </div>
    </div>

    @if($virtualMachines->count() > 0)
        <div class="mt-4 card">
            <div class="card-body">
                <h5 class="card-title">Statistiques Globales</h5>
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="p-3 rounded border">
                            <h6 class="mb-2 text-muted">VMs Actives</h6>
                            <h3 class="mb-0">{{ $stats['running'] ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded border">
                            <h6 class="mb-2 text-muted">CPU Total</h6>
                            <h3 class="mb-0">{{ $stats['total_cpu'] ?? 0 }} vCPUs</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded border">
                            <h6 class="mb-2 text-muted">RAM Totale</h6>
                            <h3 class="mb-0">{{ $stats['total_memory'] ?? 0 }} MiB</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 rounded border">
                            <h6 class="mb-2 text-muted">Stockage Total</h6>
                            <h3 class="mb-0">{{ $stats['total_disk'] ?? 0 }} GB</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
