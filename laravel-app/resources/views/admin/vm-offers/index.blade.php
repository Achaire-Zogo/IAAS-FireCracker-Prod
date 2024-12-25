@extends('layouts.admin')

@section('title', 'Gestion des Offres VM')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Gestion des Offres VM</h1>
        <a href="{{ route('admin.vm-offers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Nouvelle Offre
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if($offers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>CPU</th>
                                <th>RAM</th>
                                <th>Disque</th>
                                <th>Prix/Heure</th>
                                <th>Status</th>
                                <th>VMs Actives</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($offers as $offer)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6 class="mb-0">{{ $offer->name }}</h6>
                                                <small class="text-muted">{{ Str::limit($offer->description, 50) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $offer->vcpu_count }} vCPUs</td>
                                    <td>{{ $offer->memory_size_mib }} MiB</td>
                                    <td>{{ $offer->disk_size_gb }} GB</td>
                                    <td>${{ number_format($offer->price_per_hour, 3) }}/h</td>
                                    <td>
                                        <span class="badge {{ $offer->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $offer->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $offer->virtualMachines->where('status', 'running')->count() }}
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.vm-offers.show', $offer) }}" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="Voir les détails">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.vm-offers.edit', $offer) }}" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.vm-offers.destroy', $offer) }}" 
                                                  method="POST" 
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Supprimer"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette offre ?')">
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
                    {{ $offers->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-box h1 text-muted"></i>
                    <p class="text-muted">Aucune offre VM n'a été créée</p>
                    <a href="{{ route('admin.vm-offers.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Créer une offre
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
