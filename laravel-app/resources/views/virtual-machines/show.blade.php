<x-app-layout>
    <div class="container py-4">
        <!-- En-tête avec les actions -->
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0 h3">{{ $vm->name }}</h1>
                <p class="mb-0 text-muted">
                    <span class="badge bg-{{ $vm->status === 'running' ? 'success' : 'secondary' }}">
                        {{ ucfirst($vm->status) }}
                    </span>
                    <span class="ms-2">Created {{ $vm->created_at->diffForHumans() }}</span>
                </p>
            </div>
            <div class="gap-2 d-flex">
                @if(in_array($vm->status, ['stopped', 'created', 'error']))
                    <form action="{{ route('virtual-machines.start', $vm->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-play-fill me-2"></i>Start
                        </button>
                    </form>

                    <form action="{{ route('virtual-machines.destroy', $vm->id) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this VM?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Delete
                        </button>
                    </form>
                @elseif($vm->status === 'running')
                    <form action="{{ route('virtual-machines.stop', $vm->id) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to stop this VM?');">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-stop-fill me-2"></i>Stop
                        </button>
                    </form>
                @endif

            </div>
        </div>

        <div class="row g-4">
            <!-- Colonne de gauche -->
            <div class="col-md-8">
                <!-- Métriques système -->
                <div class="mb-4 border-0 shadow-sm card">
                    <div class="card-body">
                        <h2 class="mb-4 h5">System Metrics</h2>
                        <div class="row g-4">
                            <!-- CPU Usage -->
                            <div class="col-md-6">
                                <div class="p-3 rounded bg-light">
                                    <h6 class="mb-2">CPU Usage</h6>
                                    <div class="mb-2 progress" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar"
                                            style="width: {{ $metrics['cpu_usage'] }}%"
                                            aria-valuenow="{{ $metrics['cpu_usage'] }}"
                                            aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $metrics['cpu_usage'] }}% of {{ $vm->vcpu_count }} vCPUs</small>
                                </div>
                            </div>

                            <!-- Memory Usage -->
                            <div class="col-md-6">
                                <div class="p-3 rounded bg-light">
                                    <h6 class="mb-2">Memory Usage</h6>
                                    <div class="mb-2 progress" style="height: 10px;">
                                        @php
                                            $memoryPercentage = ($metrics['memory_usage'] / $vm->memory_size_mib) * 100;
                                        @endphp
                                        <div class="progress-bar" role="progressbar"
                                            style="width: {{ $memoryPercentage }}%"
                                            aria-valuenow="{{ $memoryPercentage }}"
                                            aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $metrics['memory_usage'] }} MiB of {{ $vm->memory_size_mib }} MiB</small>
                                </div>
                            </div>

                            <!-- Disk Usage -->
                            <div class="col-md-6">
                                <div class="p-3 rounded bg-light">
                                    <h6 class="mb-2">Disk Usage</h6>
                                    <div class="mb-2 progress" style="height: 10px;">
                                        @php
                                            $diskPercentage = ($metrics['disk_usage'] / ($vm->disk_size_gb * 1024)) * 100;
                                        @endphp
                                        <div class="progress-bar" role="progressbar"
                                            style="width: {{ $diskPercentage }}%"
                                            aria-valuenow="{{ $diskPercentage }}"
                                            aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $metrics['disk_usage'] }} MB of {{ $vm->disk_size_gb }} GB</small>
                                </div>
                            </div>

                            <!-- Network Usage -->
                            <div class="col-md-6">
                                <div class="p-3 rounded bg-light">
                                    <h6 class="mb-2">Network Usage</h6>
                                    <div class="mb-1 d-flex justify-content-between">
                                        <small class="text-success">
                                            <i class="bi bi-arrow-down me-1"></i>{{ $metrics['network_rx'] }} MB
                                        </small>
                                        <small class="text-primary">
                                            <i class="bi bi-arrow-up me-1"></i>{{ $metrics['network_tx'] }} MB
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Historique des statuts -->
                <div class="border-0 shadow-sm card">
                    <div class="card-body">
                        <h2 class="mb-4 h5">Status History</h2>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Status</th>
                                        <th>Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($statusHistory as $history)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ $history->status === 'running' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($history->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $history->created_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne de droite -->
            <div class="col-md-4">
                <!-- Informations de la VM -->
                <div class="mb-4 border-0 shadow-sm card">
                    <div class="card-body">
                        <h2 class="mb-4 h5">VM Information</h2>
                        <dl class="mb-0 row">
                            <dt class="col-sm-5">Offer</dt>
                            <dd class="col-sm-7">{{ $vm->vmOffer->name }}</dd>

                            <dt class="col-sm-5">System Image</dt>
                            <dd class="col-sm-7">{{ $vm->systemImage->name }}</dd>

                            <dt class="col-sm-5">vCPUs</dt>
                            <dd class="col-sm-7">{{ $vm->vcpu_count }}</dd>

                            <dt class="col-sm-5">Memory</dt>
                            <dd class="col-sm-7">{{ $vm->memory_size_mib }} MiB</dd>

                            <dt class="col-sm-5">Storage</dt>
                            <dd class="col-sm-7">{{ $vm->disk_size_gb }} GB</dd>

                            <dt class="col-sm-5">Cost</dt>
                            <dd class="col-sm-7">${{ number_format($totalCost, 2) }}</dd>
                        </dl>
                    </div>
                </div>

                <!-- Informations de connexion SSH -->
                <div class="border-0 shadow-sm card">
                    <div class="card-body">
                        <h2 class="mb-4 h5">SSH Connection</h2>
                        @if($vm->status === 'running')
                            <div class="mb-3">
                                <label class="form-label">SSH Command</label>
                                <div class="input-group">
                                    <span class="form-control">ssh {{ $sshInfo['username'] }} @ {{ $vm->ip_address }}</span>
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard(this.previousElementSibling)">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Download SSH Key</label>
                                <a href="{{ asset('ssh_keys_vm/' . $sshInfo['private_key']) }}" download
                                   class="btn btn-outline-primary d-block">
                                    <i class="bi bi-download me-2"></i>Download Private Key
                                </a>
                            </div>
                            <div class="mb-0 alert alert-info">
                                Your ssh root password
                                <div class="input-group">
                                    <input type="password" class="form-control" value="{{ $vm->root_password_hash }}" id="sshPassword" readonly>
                                    <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('sshPassword').type = document.getElementById('sshPassword').type === 'text' ? 'password' : 'text'">
                                        <i class="bi bi-eye" id="sshPasswordEye"></i>
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="mb-0 alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                SSH connection is only available when the VM is running.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function copyToClipboard(element) {
            element.select();
            document.execCommand('copy');

            // Optional: Show feedback
            const button = element.nextElementSibling;
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="bi bi-check"></i>';
            setTimeout(() => {
                button.innerHTML = originalHTML;
            }, 2000);
        }

        document.getElementById('sshPassword').addEventListener('focus', function() {
            document.getElementById('sshPasswordEye').classList.remove('bi-eye');
            document.getElementById('sshPasswordEye').classList.add('bi-eye-slash');
        });
        document.getElementById('sshPassword').addEventListener('blur', function() {
            document.getElementById('sshPasswordEye').classList.remove('bi-eye-slash');
            document.getElementById('sshPasswordEye').classList.add('bi-eye');
        });
    </script>
    @endpush
</x-app-layout>
