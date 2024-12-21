@extends('layouts.app')

@section('title', $virtualMachine->name)

@section('content')
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
            <div class="flex justify-between items-center">
                <h1 class="text-lg leading-6 font-medium text-gray-900">
                    {{ $virtualMachine->name }}
                </h1>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                    {{ $virtualMachine->status === 'running' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $virtualMachine->status }}
                </span>
            </div>
        </div>

        <div class="px-4 py-5 sm:p-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">ID de la VM</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $virtualMachine->vm_id }}</dd>
                </div>

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Système d'exploitation</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($virtualMachine->os_type) }}</dd>
                </div>

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">vCPU</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $virtualMachine->vcpu_count }}</dd>
                </div>

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Mémoire RAM</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $virtualMachine->mem_size_mib }}MB</dd>
                </div>

                @if($virtualMachine->ip_address)
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Adresse IP</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $virtualMachine->ip_address }}</dd>
                    </div>
                @endif

                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Créée le</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $virtualMachine->created_at->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex space-x-3">
                @if($virtualMachine->status === 'stopped')
                    <form action="{{ route('virtual-machines.start', $virtualMachine) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                            Démarrer
                        </button>
                    </form>
                @else
                    <form action="{{ route('virtual-machines.stop', $virtualMachine) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                            Arrêter
                        </button>
                    </form>
                @endif

                <form action="{{ route('virtual-machines.destroy', $virtualMachine) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700"
                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette VM ?')">
                        Supprimer
                    </button>
                </form>

                <a href="{{ route('virtual-machines.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Retour
                </a>
            </div>
        </div>
    </div>
@endsection
