@extends('layouts.admin')

@section('title', 'Historique des Machines Virtuelles')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Historique des Machines Virtuelles</h1>
        
        <!-- Filtres -->
        <div class="d-flex gap-3">
            <form action="{{ route('admin.vm-history.index') }}" method="GET" class="d-flex gap-2">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Tous les statuts</option>
                    <option value="running" {{ request('status') == 'running' ? 'selected' : '' }}>En cours</option>
                    <option value="stopped" {{ request('status') == 'stopped' ? 'selected' : '' }}>Arrêtée</option>
                    <option value="error" {{ request('status') == 'error' ? 'selected' : '' }}>Erreur</option>
                    <option value="creating" {{ request('status') == 'creating' ? 'selected' : '' }}>En création</option>
                </select>

                <input type="text" 
                       name="search" 
                       class="form-control form-control-sm" 
                       placeholder="Rechercher..."
                       value="{{ request('search') }}">

                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search"></i>
                </button>

                @if(request()->hasAny(['status', 'search']))
                    <a href="{{ route('admin.vm-history.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($history->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Machine Virtuelle</th>
                                <th>Utilisateur</th>
                                <th>Status</th>
                                <th>Détails</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($history as $entry)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $entry->created_at->format('d/m/Y') }}</span>
                                            <small class="text-muted">{{ $entry->created_at->format('H:i:s') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $entry->virtualMachine->name }}</span>
                                            <small class="text-muted">ID: {{ $entry->virtualMachine->id }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $entry->virtualMachine->user->name }}</span>
                                            <small class="text-muted">{{ $entry->virtualMachine->user->email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $entry->status === 'running' ? 'success' : 
                                            ($entry->status === 'stopped' ? 'danger' : 
                                            ($entry->status === 'error' ? 'warning' : 'info')) 
                                        }}">
                                            {{ $entry->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.all-vms.show', $entry->virtualMachine) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $history->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-clock-history h1 text-muted"></i>
                    <p class="text-muted">Aucun historique disponible</p>
                </div>
            @endif
        </div>
    </div>

    @if($history->count() > 0)
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Statistiques</h5>
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h6 class="text-muted mb-2">VMs en cours</h6>
                            <h3 class="mb-0">{{ $stats['running'] ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h6 class="text-muted mb-2">VMs arrêtées</h6>
                            <h3 class="mb-0">{{ $stats['stopped'] ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h6 class="text-muted mb-2">VMs en erreur</h6>
                            <h3 class="mb-0">{{ $stats['error'] ?? 0 }}</h3>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3">
                            <h6 class="text-muted mb-2">VMs en création</h6>
                            <h3 class="mb-0">{{ $stats['creating'] ?? 0 }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
