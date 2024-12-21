@extends('layouts.app')

@section('title', 'Machines Virtuelles')

@section('content')
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
            <div class="flex justify-between items-center">
                <h1 class="text-lg leading-6 font-medium text-gray-900">
                    Mes Machines Virtuelles
                </h1>
                <a href="{{ route('virtual-machines.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    Nouvelle VM
                </a>
            </div>
        </div>

        <div class="bg-white divide-y divide-gray-200">
            @forelse($vms as $vm)
                <div class="px-4 py-4 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">
                                {{ $vm->name }}
                            </h3>
                            <div class="mt-1 text-sm text-gray-500">
                                <p>OS: {{ ucfirst($vm->os_type) }}</p>
                                <p>CPU: {{ $vm->vcpu_count }} vCPU</p>
                                <p>RAM: {{ $vm->mem_size_mib }}MB</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $vm->status === 'running' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $vm->status }}
                            </span>
                            <div class="flex space-x-2">
                                @if($vm->status === 'stopped')
                                    <form action="{{ route('virtual-machines.start', $vm) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
                                            Démarrer
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('virtual-machines.stop', $vm) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                            Arrêter
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('virtual-machines.show', $vm) }}" 
                                   class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                    Détails
                                </a>
                                <form action="{{ route('virtual-machines.destroy', $vm) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-gray-600 text-white px-3 py-1 rounded hover:bg-gray-700"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette VM ?')">
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-5 text-center text-gray-500">
                    Aucune machine virtuelle trouvée. Créez-en une nouvelle !
                </div>
            @endforelse
        </div>
    </div>
@endsection
