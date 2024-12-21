<x-app-layout>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="card-title h4 mb-4">{{ __('Create New Virtual Machine') }}</h2>

                        <form method="POST" action="{{ route('virtual-machines.store') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="name" class="form-label">{{ __('VM Name') }}</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="os_type" class="form-label">{{ __('Operating System') }}</label>
                                <select class="form-select @error('os_type') is-invalid @enderror" 
                                        id="os_type" name="os_type" required>
                                    <option value="">{{ __('Select OS') }}</option>
                                    <option value="ubuntu" {{ old('os_type') == 'ubuntu' ? 'selected' : '' }}>Ubuntu</option>
                                    <option value="debian" {{ old('os_type') == 'debian' ? 'selected' : '' }}>Debian</option>
                                    <option value="centos" {{ old('os_type') == 'centos' ? 'selected' : '' }}>CentOS</option>
                                </select>
                                @error('os_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="vcpu_count" class="form-label">{{ __('vCPUs') }}</label>
                                    <select class="form-select @error('vcpu_count') is-invalid @enderror" 
                                            id="vcpu_count" name="vcpu_count" required>
                                        @foreach([1, 2, 4, 8] as $cpu)
                                            <option value="{{ $cpu }}" {{ old('vcpu_count') == $cpu ? 'selected' : '' }}>
                                                {{ $cpu }} {{ __('Core(s)') }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('vcpu_count')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="mem_size_mib" class="form-label">{{ __('Memory (MB)') }}</label>
                                    <select class="form-select @error('mem_size_mib') is-invalid @enderror" 
                                            id="mem_size_mib" name="mem_size_mib" required>
                                        @foreach([512, 1024, 2048, 4096] as $mem)
                                            <option value="{{ $mem }}" {{ old('mem_size_mib') == $mem ? 'selected' : '' }}>
                                                {{ $mem }} MB
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('mem_size_mib')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-cloud-plus me-2"></i>{{ __('Create Virtual Machine') }}
                                </button>
                                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>{{ __('Back to Dashboard') }}
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
