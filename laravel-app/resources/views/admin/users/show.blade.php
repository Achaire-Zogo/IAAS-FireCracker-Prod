@extends('layouts.admin')

@section('title', 'Détails de l\'Utilisateur')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Détails de l'Utilisateur</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Informations Générales</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nom</label>
                                <p class="form-control-plaintext">{{ $user->name }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <p class="form-control-plaintext">{{ $user->email }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rôle</label>
                                <p class="form-control-plaintext">
                                    <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : 'bg-primary' }}">
                                        {{ $user->role }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date d'inscription</label>
                                <p class="form-control-plaintext">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Machines Virtuelles</h6>
                        @if($user->virtualMachines->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nom</th>
                                            <th>Status</th>
                                            <th>Créée le</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->virtualMachines as $vm)
                                            <tr>
                                                <td>{{ $vm->name }}</td>
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
                            <p class="text-muted">Aucune machine virtuelle</p>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Retour
                        </a>
                        <div>
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary me-2">
                                <i class="bi bi-pencil me-2"></i>Modifier
                            </a>
                            @if($user->id !== Auth::id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                        <i class="bi bi-trash me-2"></i>Supprimer
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
