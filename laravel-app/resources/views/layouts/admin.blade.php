<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Administration</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Scripts -->

</head>
<body>
    <div class="min-vh-100 d-flex">
        <!-- Sidebar -->
        <div class="text-white bg-dark" style="width: 280px;">
            <div class="d-flex flex-column h-100">
                <div class="p-3">
                    <h5 class="mb-3">Administration</h5>
                    <nav class="nav flex-column">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link text-white {{ request()->routeIs('admin.dashboard') ? 'active bg-primary' : '' }}">
                            <i class="bi bi-home me-2"></i>Tableau de bord
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="nav-link text-white {{ request()->routeIs('admin.users.*') ? 'active bg-primary' : '' }}">
                            <i class="bi bi-people me-2"></i>Utilisateurs
                        </a>
                        <a href="{{ route('admin.vm-offers.index') }}" class="nav-link text-white {{ request()->routeIs('admin.vm-offers.*') ? 'active bg-primary' : '' }}">
                            <i class="bi bi-box me-2"></i>Offres VM
                        </a>
                        <a href="{{ route('admin.system-images.index') }}" class="nav-link text-white {{ request()->routeIs('admin.system-images.*') ? 'active bg-primary' : '' }}">
                            <i class="bi bi-hdd me-2"></i>Images Système
                        </a>
                        <a href="{{ route('admin.vm-history.index') }}" class="nav-link text-white {{ request()->routeIs('admin.vm-history.*') ? 'active bg-primary' : '' }}">
                            <i class="bi bi-clock-history me-2"></i>Historique VMs
                        </a>
                        <a href="{{ route('admin.ssh-keys.index') }}" class="nav-link text-white {{ request()->routeIs('admin.ssh-keys.*') ? 'active bg-primary' : '' }}">
                            <i class="bi bi-key me-2"></i>Clés SSH
                        </a>
                        <a href="{{ route('admin.all-vms.index') }}" class="nav-link text-white {{ request()->routeIs('admin.all-vms.*') ? 'active bg-primary' : '' }}">
                            <i class="bi bi-pc-display me-2"></i>Toutes les VMs
                        </a>
                    </nav>
                </div>
                <div class="p-3 mt-auto">
                    <a href="{{ route('dashboard.index') }}" class="text-white nav-link">
                        <i class="bi bi-arrow-left me-2"></i>Retour au site
                    </a>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="flex-grow-1">
            <!-- Top navbar -->
            <nav class="bg-white navbar navbar-expand-lg navbar-light border-bottom">
                <div class="container-fluid">
                    <span class="navbar-text">
                        @yield('title', 'Dashboard')
                    </span>
                    <div class="navbar-nav ms-auto">
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page content -->
            <div class="py-4 container-fluid">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
