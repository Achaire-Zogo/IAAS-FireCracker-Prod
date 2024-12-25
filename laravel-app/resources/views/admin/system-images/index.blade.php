@extends('layouts.admin')

@section('title', 'Images Système')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Images Système</h1>
        <a href="{{ route('admin.system-images.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Nouvelle Image
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            @if($images->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Chemin du Kernel</th>
                                <th>Chemin du Rootfs</th>
                                <th>Status</th>
                                <th>VMs Actives</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($images as $image)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6 class="mb-0">{{ $image->name }}</h6>
                                                <small class="text-muted">{{ Str::limit($image->description, 50) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ Str::limit($image->kernel_image_path, 30) }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ Str::limit($image->rootfs_path, 30) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $image->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $image->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $image->virtualMachines->where('status', 'running')->count() }}
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.system-images.show', $image) }}" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="Voir les détails">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.system-images.edit', $image) }}" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.system-images.destroy', $image) }}" 
                                                  method="POST" 
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Supprimer"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette image ?')">
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
                    {{ $images->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bi bi-hdd h1 text-muted"></i>
                    <p class="text-muted">Aucune image système n'a été créée</p>
                    <a href="{{ route('admin.system-images.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Créer une image
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
