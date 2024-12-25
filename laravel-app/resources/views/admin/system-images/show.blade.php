@extends('layouts.admin')

@section('title', 'Détails de l\'Image Système')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Détails de l'Image Système</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Informations Générales</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nom</label>
                                <p class="form-control-plaintext">{{ $image->name }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <p class="form-control-plaintext">
                                    <span class="badge {{ $image->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $image->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <p class="form-control-plaintext">{{ $image->description ?: 'Aucune description' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Chemins des Fichiers</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Chemin du Kernel</label>
                                <p class="form-control-plaintext">{{ $image->kernel_image_path }}</p>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Chemin du Rootfs</label>
                                <p class="form-control-plaintext">{{ $image->rootfs_path }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Machines Virtuelles Utilisant cette Image</h6>
                        @if($image->virtualMachines->count() > 0)
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
                                        @foreach($image->virtualMachines as $vm)
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
                            <p class="text-muted">Aucune machine virtuelle n'utilise cette image</p>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.system-images.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Retour
                        </a>
                        <div>
                            <a href="{{ route('admin.system-images.edit', $image) }}" class="btn btn-primary me-2">
                                <i class="bi bi-pencil me-2"></i>Modifier
                            </a>
                            <form action="{{ route('admin.system-images.destroy', $image) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" 
                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette image ?')">
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
