<x-app-layout>
    <div class="container py-4">
        <!-- Statistiques -->
        <div class="mb-5 row g-4">
            @if(Auth::user()->isAdmin())
            <div class="col-12 mb-3">
                <div class="alert alert-info d-flex align-items-center justify-content-between">
                    <div>
                        <i class="bi bi-shield-lock me-2"></i>
                        <strong>Administration Access</strong> - You have administrative privileges
                    </div>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                        <i class="bi bi-gear me-2"></i>Access Admin Panel
                    </a>
                </div>
            </div>
            @endif
            <div class="col-md-3">
                <div class="border-0 shadow-sm card h-100">
                    <div class="card-body">
                        <h6 class="mb-2 text-muted">Total VMs</h6>
                        <h2 class="mb-0 display-6">{{ $vmStats['total'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border-0 shadow-sm card h-100">
                    <div class="card-body">
                        <h6 class="mb-2 text-muted">Running VMs</h6>
                        <h2 class="mb-0 display-6 text-success">{{ $vmStats['running'] }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border-0 shadow-sm card h-100">
                    <div class="card-body">
                        <h6 class="mb-2 text-muted">Total Cost</h6>
                        <h2 class="mb-0 display-6 text-primary">${{ number_format($vmStats['total_cost'], 2) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border-0 shadow-sm card h-100">
                    <div class="card-body">
                        <h6 class="mb-2 text-muted">Resources</h6>
                        <div class="d-flex flex-column">
                            <span><strong>{{ $vmStats['total_cpu'] }}</strong> vCPUs</span>
                            <span><strong>{{ $vmStats['total_memory'] }}</strong> MiB RAM</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Offres de VM -->
        <div class="mb-5 border-0 shadow-sm card">
            <div class="card-body">
                <h3 class="mb-4 h5">Available VM Offers</h3>
                <div class="row g-4">
                    @foreach ($offers as $offer)
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h4 class="mb-3 h5">{{ $offer->name }}</h4>
                                <p class="mb-4 text-muted">{{ $offer->description }}</p>
                                <ul class="mb-4 list-unstyled">
                                    <li class="mb-2">
                                        <i class="bi bi-cpu me-2"></i>
                                        {{ $offer->cpu_count }} vCPUs
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-memory me-2"></i>
                                        {{ $offer->memory_size_mib }} MiB RAM
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-hdd me-2"></i>
                                        {{ $offer->disk_size_gb }} GB Storage
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-currency-dollar me-2"></i>
                                        <strong>${{ number_format($offer->price_per_hour, 1) }}</strong>/hour
                                    </li>
                                </ul>
                                <a href="{{ route('virtual-machines.create', ['offer' => $offer->id]) }}"
                                   class="btn btn-primary w-100">
                                    Create VM with this offer
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Liste des VMs -->
        <div class="border-0 shadow-sm card">
            <div class="card-body">
                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <h3 class="mb-0 h5">Your Virtual Machines</h3>
                    <a href="{{ route('virtual-machines.create') }}"
                       class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>New VM
                    </a>
                </div>

                @if($virtualMachines->isEmpty())
                <div class="py-5 text-center">
                    <h4 class="mb-2 h6 text-muted">No Virtual Machines Yet</h4>
                    <p class="mb-0 text-muted">Create your first virtual machine to get started.</p>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Configuration</th>
                                <th>IP Address</th>
                                <th>Cost</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($virtualMachines as $vm)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $vm->name }}</div>
                                </td>
                                <td>
                                    @if($vm->status === 'running')
                                    <span class="badge bg-success">Running</span>
                                    @elseif($vm->status === 'stopped')
                                    <span class="badge bg-secondary">Stopped</span>
                                    @else
                                    <span class="badge bg-warning">{{ ucfirst($vm->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="small">
                                        <div>{{ $vm->vcpu_count }} vCPUs</div>
                                        <div>{{ $vm->memory_size_mib }} MiB RAM</div>
                                        <div>{{ $vm->disk_size_gb }} GB Storage</div>
                                    </div>
                                </td>
                                <td>
                                    @if($vm->ip_address)
                                    <div>{{ $vm->ip_address }}</div>
                                    <div class="small text-muted">SSH: Port {{ $vm->ssh_port }}</div>
                                    @else
                                    <span class="text-muted">Not assigned</span>
                                    @endif
                                </td>
                                <td>${{ number_format($vm->total_cost, 2) }}</td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('virtual-machines.show', $vm->id) }}" class="btn btn-sm btn-outline-primary">
                                           Details
                                        </a>
                                        &nbsp;
                                        @if($vm->status === 'stopped')
                                        <form action="{{ route('virtual-machines.start', $vm->id) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to start this VM?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Start</button>
                                        </form>
                                        @elseif($vm->status === 'running')
                                        <form action="{{ route('virtual-machines.stop', $vm->id) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to stop this VM?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning">Stop</button>
                                        </form>
                                        @endif
                                    </div>
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
</x-app-layout>
