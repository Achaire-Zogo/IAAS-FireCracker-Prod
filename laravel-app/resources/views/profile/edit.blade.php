@extends('layouts.profile')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="bi bi-person-circle text-primary h3 mb-0"></i>
                        </div>
                        <div>
                            <h4 class="mb-1">Profil</h4>
                            <p class="text-muted mb-0">Gérez les informations de votre compte</p>
                        </div>
                    </div>

                    <form method="post" action="{{ route('profile.update') }}" class="mb-5">
                        @csrf
                        @method('patch')

                        <div class="mb-4">
                            <label for="name" class="form-label">Nom</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $user->name) }}"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $user->email) }}"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Sauvegarder
                        </button>
                    </form>

                    <hr class="my-4">

                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="bi bi-shield-lock text-warning h3 mb-0"></i>
                        </div>
                        <div>
                            <h4 class="mb-1">Sécurité</h4>
                            <p class="text-muted mb-0">Mettez à jour votre mot de passe</p>
                        </div>
                    </div>

                    <form method="post" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="mb-4">
                            <label for="current_password" class="form-label">Mot de passe actuel</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key"></i></span>
                                <input type="password" 
                                       class="form-control @error('current_password') is-invalid @enderror" 
                                       id="current_password" 
                                       name="current_password"
                                       required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Nouveau mot de passe</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password"
                                       required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" 
                                       class="form-control"
                                       id="password_confirmation" 
                                       name="password_confirmation"
                                       required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-shield-check me-2"></i>Mettre à jour le mot de passe
                        </button>
                    </form>

                    <hr class="my-4">

                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-danger bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="bi bi-exclamation-triangle text-danger h3 mb-0"></i>
                        </div>
                        <div>
                            <h4 class="mb-1">Zone dangereuse</h4>
                            <p class="text-muted mb-0">Suppression définitive du compte</p>
                        </div>
                    </div>

                    <form method="post" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.');">
                        @csrf
                        @method('delete')

                        <div class="mb-4">
                            <label for="password_delete" class="form-label">Mot de passe</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key"></i></span>
                                <input type="password" 
                                       class="form-control @error('password_delete') is-invalid @enderror" 
                                       id="password_delete" 
                                       name="password"
                                       required
                                       placeholder="Confirmez votre mot de passe pour supprimer le compte">
                                @error('password_delete')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text text-danger">
                                <i class="bi bi-exclamation-circle me-1"></i>
                                Une fois votre compte supprimé, toutes vos ressources et données seront définitivement effacées.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Supprimer le compte
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
