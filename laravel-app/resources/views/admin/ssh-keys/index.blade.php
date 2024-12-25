@extends('layouts.admin')

@section('title', 'Gestion des Clés SSH')

@section('content')
<div class="container-fluid">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h1 class="mb-0 h3">Clés SSH</h1>

        <!-- Filtres -->
        <div class="gap-3 d-flex">
            <form action="{{ route('admin.ssh-keys.index') }}" method="GET" class="gap-2 d-flex">
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

                @if(request()->hasAny(['user', 'search']))
                    <a href="{{ route('admin.ssh-keys.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($sshKeys->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Utilisateur</th>
                                <th>Empreinte</th>
                                <th>Créée le</th>
                                <th>Dernière utilisation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sshKeys as $key)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $key->name }}</span>
                                            <small class="text-muted">ID: {{ $key->id }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $key->user->name }}</span>
                                            <small class="text-muted">{{ $key->user->email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <code class="small">{{ $key->fingerprint }}</code>
                                    </td>
                                    <td>{{ $key->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($key->last_used_at)
                                            {{ $key->last_used_at->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-muted">Jamais utilisée</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.ssh-keys.show', $key) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="Voir les détails">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <form action="{{ route('admin.ssh-keys.destroy', $key) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Supprimer"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette clé SSH ?')">
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
                    {{ $sshKeys->links() }}
                </div>
            @else
                <div class="py-4 text-center">
                    <i class="bi bi-key h1 text-muted"></i>
                    <p class="text-muted">Aucune clé SSH trouvée</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
