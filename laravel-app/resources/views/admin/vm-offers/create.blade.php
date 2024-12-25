@extends('layouts.admin')

@section('title', 'Créer une Offre VM')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Nouvelle Offre VM</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.vm-offers.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom de l'offre</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="vcpu_count" class="form-label">Nombre de vCPUs</label>
                                <input type="number" class="form-control @error('vcpu_count') is-invalid @enderror" 
                                       id="vcpu_count" name="vcpu_count" value="{{ old('vcpu_count') }}" required min="1">
                                @error('vcpu_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="memory_size_mib" class="form-label">RAM (MiB)</label>
                                <input type="number" class="form-control @error('memory_size_mib') is-invalid @enderror" 
                                       id="memory_size_mib" name="memory_size_mib" value="{{ old('memory_size_mib') }}" required min="128">
                                @error('memory_size_mib')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="disk_size_gb" class="form-label">Disque (GB)</label>
                                <input type="number" class="form-control @error('disk_size_gb') is-invalid @enderror" 
                                       id="disk_size_gb" name="disk_size_gb" value="{{ old('disk_size_gb') }}" required min="1">
                                @error('disk_size_gb')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="price_per_hour" class="form-label">Prix par heure ($)</label>
                            <input type="number" class="form-control @error('price_per_hour') is-invalid @enderror" 
                                   id="price_per_hour" name="price_per_hour" value="{{ old('price_per_hour') }}" 
                                   required step="0.001" min="0">
                            @error('price_per_hour')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input @error('is_active') is-invalid @enderror" 
                                       id="is_active" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Activer cette offre</label>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.vm-offers.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Créer l'offre
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
