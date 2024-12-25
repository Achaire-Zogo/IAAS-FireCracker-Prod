@extends('layouts.admin')

@section('title', 'Créer une Image Système')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Nouvelle Image Système</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.system-images.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom de l'image</label>
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

                        <div class="mb-3">
                            <label for="kernel_image_path" class="form-label">Chemin du Kernel</label>
                            <input type="text" class="form-control @error('kernel_image_path') is-invalid @enderror" 
                                   id="kernel_image_path" name="kernel_image_path" value="{{ old('kernel_image_path') }}" required>
                            <div class="form-text">Chemin absolu vers l'image du kernel</div>
                            @error('kernel_image_path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="rootfs_path" class="form-label">Chemin du Rootfs</label>
                            <input type="text" class="form-control @error('rootfs_path') is-invalid @enderror" 
                                   id="rootfs_path" name="rootfs_path" value="{{ old('rootfs_path') }}" required>
                            <div class="form-text">Chemin absolu vers le système de fichiers root</div>
                            @error('rootfs_path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input @error('is_active') is-invalid @enderror" 
                                       id="is_active" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Activer cette image</label>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.system-images.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Créer l'image
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
