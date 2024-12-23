<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $virtualMachine->name }}
            </h2>
            <div class="space-x-2">
                @if($virtualMachine->status === 'stopped')
                <form action="{{ route('virtual-machines.start', $virtualMachine->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition-colors">
                        Start VM
                    </button>
                </form>
                @elseif($virtualMachine->status === 'running')
                <form action="{{ route('virtual-machines.stop', $virtualMachine->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 transition-colors">
                        Stop VM
                    </button>
                </form>
                @endif
                <a href="{{ route('dashboard.index') }}"
                    class="bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700 transition-colors">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- État et métriques -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-full">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Status & Metrics</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-sm text-gray-600">Status</div>
                            <div class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($virtualMachine->status === 'running') bg-green-100 text-green-800
                                    @elseif($virtualMachine->status === 'stopped') bg-gray-100 text-gray-800
                                    @elseif($virtualMachine->status === 'error') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ $virtualMachine->status }}
                                </span>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-sm text-gray-600">CPU Usage</div>
                            <div class="mt-1 text-xl font-semibold">
                                {{ number_format($virtualMachine->cpu_usage_percent, 1) }}%
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-sm text-gray-600">Memory Usage</div>
                            <div class="mt-1 text-xl font-semibold">
                                {{ $virtualMachine->memory_usage_mib }} / {{ $virtualMachine->memory_size_mib }} MiB
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-sm text-gray-600">Network Traffic</div>
                            <div class="mt-1 text-sm">
                                ↓ {{ number_format($virtualMachine->network_rx_bytes / 1024 / 1024, 2) }} MB
                                ↑ {{ number_format($virtualMachine->network_tx_bytes / 1024 / 1024, 2) }} MB
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuration -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-full">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Configuration</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-2">System</h4>
                            <dl class="grid grid-cols-3 gap-4">
                                <div class="col-span-1 text-sm text-gray-600">OS</div>
                                <div class="col-span-2 text-sm">{{ $virtualMachine->systemImage->name }}</div>
                                
                                <div class="col-span-1 text-sm text-gray-600">vCPUs</div>
                                <div class="col-span-2 text-sm">{{ $virtualMachine->vcpu_count }}</div>
                                
                                <div class="col-span-1 text-sm text-gray-600">Memory</div>
                                <div class="col-span-2 text-sm">{{ $virtualMachine->memory_size_mib }} MiB</div>
                                
                                <div class="col-span-1 text-sm text-gray-600">Disk</div>
                                <div class="col-span-2 text-sm">{{ $virtualMachine->disk_size_gb }} GB</div>
                                
                                <div class="col-span-1 text-sm text-gray-600">Offer</div>
                                <div class="col-span-2 text-sm">{{ $virtualMachine->vmOffer->name }}</div>
                            </dl>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Network</h4>
                            <dl class="grid grid-cols-3 gap-4">
                                <div class="col-span-1 text-sm text-gray-600">IP Address</div>
                                <div class="col-span-2 text-sm">{{ $virtualMachine->ip_address }}</div>
                                
                                <div class="col-span-1 text-sm text-gray-600">MAC Address</div>
                                <div class="col-span-2 text-sm font-mono">{{ $virtualMachine->mac_address }}</div>
                                
                                <div class="col-span-1 text-sm text-gray-600">SSH Port</div>
                                <div class="col-span-2 text-sm">{{ $virtualMachine->ssh_port }}</div>
                                
                                <div class="col-span-1 text-sm text-gray-600">TAP Device</div>
                                <div class="col-span-2 text-sm">{{ $virtualMachine->tap_device_name }}</div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Billing -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-full">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Billing</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-sm text-gray-600">Price per Hour</div>
                            <div class="mt-1 text-xl font-semibold">
                                ${{ number_format($virtualMachine->vmOffer->price_per_hour, 3) }}
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-sm text-gray-600">Total Running Hours</div>
                            <div class="mt-1 text-xl font-semibold">
                                {{ number_format($virtualMachine->total_running_hours, 1) }}
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="text-sm text-gray-600">Total Cost</div>
                            <div class="mt-1 text-xl font-semibold">
                                ${{ number_format($virtualMachine->total_cost, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logs et erreurs -->
            @if($virtualMachine->last_error_message)
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-full">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Last Error</h3>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="text-sm text-gray-600">
                            {{ $virtualMachine->last_error_time->format('Y-m-d H:i:s') }}
                        </div>
                        <div class="mt-2 text-sm text-red-800">
                            {{ $virtualMachine->last_error_message }}
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
