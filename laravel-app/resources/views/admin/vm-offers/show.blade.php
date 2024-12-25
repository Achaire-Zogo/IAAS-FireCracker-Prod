@extends('layouts.admin')

@section('title', 'Détails de l\'Offre VM')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Détails de l'Offre VM</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Informations Générales</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nom</label>
                                <p class="form-control-plaintext">{{ $offer->name }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <p class="form-control-plaintext">
                                    <span class="badge {{ $offer->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $offer->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <p class="form-control-plaintext">{{ $offer->description ?: 'Aucune description' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Spécifications Techniques</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">vCPUs</label>
                                <p class="form-control-plaintext">{{ $offer->vcpu_count }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">RAM</label>
                                <p class="form-control-plaintext">{{ $offer->memory_size_mib }} MiB</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Disque</label>
                                <p class="form-control-plaintext">{{ $offer->disk_size_gb }} GB</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Tarification</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Prix par heure</label>
                                <p class="form-control-plaintext">${{ number_format($offer->price_per_hour, 3) }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prix par mois (estimé)</label>
                                <p class="form-control-plaintext">${{ number_format($offer->price_per_hour * 24 * 30, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Machines Virtuelles Utilisant cette Offre</h6>
                        @if($offer->virtualMachines->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nom</th>
                                            <th>Utilisateur</th>
                                            <th>Status</th>
                                            <th>Créée le</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($offer->virtualMachines as $vm)
                                            <tr>
                                                <td>{{ $vm->name }}</td>
                                                <td>{{ $vm->user->name }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $vm->status === 'running' ? 'success' : ($vm->status === 'stopped' ? 'danger' : 'warning') }}">
                                                        {{ $vm->status }}
                                                    </span>
                                                </td>
                                                <td>{{ $vm->created_at->format('d/m/Y') }}</td>
                                                <td>
                                                    <a href="{{ route('admin.all-vms.show', $vm) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">Aucune machine virtuelle n'utilise cette offre</p>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.vm-offers.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Retour
                        </a>
                        <div>
                            <a href="{{ route('admin.vm-offers.edit', $offer) }}" class="btn btn-primary me-2">
                                <i class="bi bi-pencil me-2"></i>Modifier
                            </a>
                            <form action="{{ route('admin.vm-offers.destroy', $offer) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" 
                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette offre ?')">
                                    <i class="bi bi-trash me-2"></i>Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
