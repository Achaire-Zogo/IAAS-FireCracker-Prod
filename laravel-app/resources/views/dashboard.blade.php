<x-app-layout>
    <div class="container py-4">
        <div class="mb-4 row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="mb-0 h4">{{ __('Virtual Machines') }}</h2>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createVMModal">
                        <i class="bi bi-plus-circle me-2"></i>{{ __('New VM') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="mb-4 row">
            <div class="col-md-4">
                <div class="border-0 shadow-sm card">
                    <div class="text-center card-body">
                        <h3 class="display-4 text-primary">{{ $virtualMachines->count() }}</h3>
                        <p class="mb-0 text-muted">{{ __('Total VMs') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border-0 shadow-sm card">
                    <div class="text-center card-body">
                        <h3 class="display-4 text-success">{{ $virtualMachines->where('status', 'running')->count() }}</h3>
                        <p class="mb-0 text-muted">{{ __('Running') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border-0 shadow-sm card">
                    <div class="text-center card-body">
                        <h3 class="display-4 text-secondary">{{ $virtualMachines->where('status', 'stopped')->count() }}</h3>
                        <p class="mb-0 text-muted">{{ __('Stopped') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-0 shadow-sm card">
            <div class="card-body">
                @if($virtualMachines->isEmpty())
                    <div class="py-4 text-center">
                        <i class="bi bi-cloud-slash display-4 text-muted"></i>
                        <p class="mt-3 mb-0">{{ __('No virtual machines found') }}</p>
                        <p class="text-muted">{{ __('Click the "New VM" button to create your first virtual machine.') }}</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('IP Address') }}</th>
                                    <th>{{ __('SSH Port') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($virtualMachines as $vm)
                                    <tr>
                                        <td>{{ $vm->name }}</td>
                                        <td>
                                            <span class="badge bg-{{ $vm->status === 'running' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($vm->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $vm->ip_address ?? '-' }}</td>
                                        <td>{{ $vm->ssh_port ?? '-' }}</td>
                                        <td>
                                            <div class="btn-group">
                                                @if($vm->status === 'running')
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                            onclick="document.getElementById('stop-form-{{ $vm->id }}').submit()">
                                                        <i class="bi bi-stop-circle"></i>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline-success"
                                                            onclick="document.getElementById('start-form-{{ $vm->id }}').submit()">
                                                        <i class="bi bi-play-circle"></i>
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal" data-bs-target="#sshModal{{ $vm->id }}">
                                                    <i class="bi bi-terminal"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        onclick="confirmDelete({{ $vm->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>

                                            <form id="start-form-{{ $vm->id }}"
                                                  action="{{ route('virtual-machines.start', $vm->id) }}"
                                                  method="POST" class="d-none">
                                                @csrf
                                            </form>
                                            <form id="stop-form-{{ $vm->id }}"
                                                  action="{{ route('virtual-machines.stop', $vm->id) }}"
                                                  method="POST" class="d-none">
                                                @csrf
                                            </form>
                                            <form id="delete-form-{{ $vm->id }}"
                                                  action="{{ route('virtual-machines.destroy', $vm->id) }}"
                                                  method="POST" class="d-none">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Create VM Modal -->
    <div class="modal fade" id="createVMModal" tabindex="-1" aria-labelledby="createVMModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createVMModalLabel">{{ __('Create New Virtual Machine') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('virtual-machines.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('VM Name') }}</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Operating System') }}</label>
                            <div class="row g-3 os-selector">
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="os_type" id="ubuntu" value="ubuntu" required>
                                    <label class="btn btn-outline-light w-100 text-start h-100" for="ubuntu">
                                        <img src="{{ asset('assets/images/ubuntu.svg') }}" alt="Ubuntu" class="mb-2 os-logo">
                                        <div>
                                            <strong>Ubuntu</strong>
                                            <small class="d-block text-muted">22.04 LTS</small>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="os_type" id="debian" value="debian">
                                    <label class="btn btn-outline-light w-100 text-start h-100" for="debian">
                                        <img src="{{ asset('assets/images/debian.svg') }}" alt="Debian" class="mb-2 os-logo">
                                        <div>
                                            <strong>Debian</strong>
                                            <small class="d-block text-muted">11 Bullseye</small>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="os_type" id="centos" value="centos">
                                    <label class="btn btn-outline-light w-100 text-start h-100" for="centos">
                                        <img src="{{ asset('assets/images/centos.svg') }}" alt="CentOS" class="mb-2 os-logo">
                                        <div>
                                            <strong>CentOS</strong>
                                            <small class="d-block text-muted">Stream 9</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            @error('os_type')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="vcpu_count" class="form-label">{{ __('vCPUs') }}</label>
                                <select class="form-select @error('vcpu_count') is-invalid @enderror"
                                        id="vcpu_count" name="vcpu_count" required>
                                    @foreach([1, 2, 4, 8] as $cpu)
                                        <option value="{{ $cpu }}">{{ $cpu }} {{ __('Core(s)') }}</option>
                                    @endforeach
                                </select>
                                @error('vcpu_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 col-md-6">
                                <label for="mem_size_mib" class="form-label">{{ __('Memory (MB)') }}</label>
                                <select class="form-select @error('mem_size_mib') is-invalid @enderror"
                                        id="mem_size_mib" name="mem_size_mib" required>
                                    @foreach([512, 1024, 2048, 4096] as $mem)
                                        <option value="{{ $mem }}">{{ $mem }} MB</option>
                                    @endforeach
                                </select>
                                @error('mem_size_mib')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cloud-plus me-2"></i>{{ __('Create VM') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SSH Details Modals -->
    @foreach($virtualMachines as $vm)
        <div class="modal fade" id="sshModal{{ $vm->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('SSH Connection Details') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('SSH Command') }}</label>
                            <div class="input-group">
                                <input type="text" class="form-control" readonly
                                       value="ssh -p {{ $vm->ssh_port }} root@{{ $vm->ip_address }}">
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard(this)">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                        @if($vm->sshKey)
                            <div class="mb-3">
                                <label class="form-label">{{ __('Private Key') }}</label>
                                <div class="form-control" style="height: auto;">
                                    <pre class="mb-0" style="white-space: pre-wrap;">{{ $vm->sshKey->private_key }}</pre>
                                </div>
                                <button class="mt-2 btn btn-outline-primary btn-sm" onclick="downloadPrivateKey({{ $vm->id }})">
                                    <i class="bi bi-download me-2"></i>{{ __('Download Private Key') }}
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    @push('styles')
    <style>
        .btn-check + .btn-outline-light {
            border: 1px solid #dee2e6;
            transition: all 0.2s ease-in-out;
        }

        .btn-check:checked + .btn-outline-light {
            background-color: rgba(var(--bs-primary-rgb), 0.1);
            border-color: var(--bs-primary);
            color: var(--bs-body-color);
        }

        .btn-check + .btn-outline-light:hover {
            background-color: rgba(var(--bs-primary-rgb), 0.05);
            border-color: var(--bs-primary);
            color: var(--bs-body-color);
        }

        .os-selector .btn {
            min-height: 100px;
            padding: 1rem;
        }

        .os-selector img {
            width: 48px;
            height: 48px;
            margin-bottom: 0.5rem;
            display: block;
        }

        .os-selector .btn:hover img,
        .btn-check:checked + .btn img {
            transform: scale(1.1);
            transition: transform 0.2s ease-in-out;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        function confirmDelete(vmId) {
            if (confirm('{{ __("Are you sure you want to delete this virtual machine?") }}')) {
                document.getElementById('delete-form-' + vmId).submit();
            }
        }

        function copyToClipboard(button) {
            const input = button.parentElement.querySelector('input');
            input.select();
            document.execCommand('copy');

            const icon = button.querySelector('i');
            icon.classList.remove('bi-clipboard');
            icon.classList.add('bi-clipboard-check');

            setTimeout(() => {
                icon.classList.remove('bi-clipboard-check');
                icon.classList.add('bi-clipboard');
            }, 2000);
        }

        function downloadPrivateKey(vmId) {
            const privateKey = document.querySelector(`#sshModal${vmId} pre`).textContent;
            const element = document.createElement('a');
            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(privateKey));
            element.setAttribute('download', `vm_${vmId}_private_key.pem`);
            element.style.display = 'none';
            document.body.appendChild(element);
            element.click();
            document.body.removeChild(element);
        }
    </script>
    @endpush
</x-app-layout>
