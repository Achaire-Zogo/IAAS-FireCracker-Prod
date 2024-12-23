<x-app-layout>
    <!-- Hero Section -->
    <div class="py-5 text-white bg-primary">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="mb-3 display-4 fw-bold">{{ __('Deploy Virtual Machines in Seconds') }}</h1>
                    <p class="mb-4 lead">{{ __('Experience lightning-fast VM deployment with our Firecracker-powered platform. Get started in minutes with secure, isolated, and high-performance virtual machines.') }}</p>
                    <div class="gap-2 d-grid d-md-flex">
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-4 btn btn-light btn-lg">
                                <i class="bi bi-speedometer2 me-2"></i>{{ __('Go to Dashboard') }}
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="px-4 btn btn-light btn-lg">
                                <i class="bi bi-person-plus me-2"></i>{{ __('Get Started') }}
                            </a>
                            <a href="{{ route('login') }}" class="px-4 btn btn-outline-light btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>{{ __('Sign In') }}
                            </a>
                        @endauth
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <img src="{{ asset('assets/images/iaas_firecracker.jpeg') }}" alt="IAASFirecracker" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="py-5">
        <div class="container">
            <div class="mb-5 text-center">
                <h2 class="fw-bold">{{ __('Why Choose Our Platform?') }}</h2>
                <p class="text-muted">{{ __('Discover the benefits of our modern VM management solution') }}</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="border-0 shadow-sm card h-100">
                        <div class="p-4 text-center card-body">
                            <div class="mb-3 text-white feature-icon bg-primary bg-gradient rounded-circle">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-lightning-bolt"><path d="M13 2L13.016 14h7l-.016 8H13z"></path><path d="M6 9h4"></path><path d="M3.051 15H6V9h4.082l3-4 3 4h3.882l-4.769 8.852 4.769 8.847z"></path></svg>
                            </div>
                            <h5 class="card-title">{{ __('Lightning Fast') }}</h5>
                            <p class="card-text text-muted">
                                {{ __('Launch new VMs in milliseconds with Firecracker\'s innovative MicroVM technology.') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border-0 shadow-sm card h-100">
                        <div class="p-4 text-center card-body">
                            <div class="mb-3 text-white feature-icon bg-primary bg-gradient rounded-circle">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shield"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="4"></circle><line x1="2.25" y1="12" x2="22.25" y2="12"></line><line x1="12" y1="2.25" x2="12" y2="22.25"></line><line x1="4.47" y1="7.44" x2="19.53" y2="16.56"></line><line x1="16.56" y1="5.17" x2="6.97" y2="18.25"></line></svg>
                            </div>
                            <h5 class="card-title">{{ __('Secure by Design') }}</h5>
                            <p class="card-text text-muted">
                                {{ __('Benefit from strong isolation and security with our virtualization technology.') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border-0 shadow-sm card h-100">
                        <div class="p-4 text-center card-body">
                            <div class="mb-3 text-white feature-icon bg-primary bg-gradient rounded-circle">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-settings"><circle cx="12" cy="12" r="3"></circle><path d="M19.73 5.29a2 2 0 00-1.38-.98l-8.57 4.25a2 2 0 00-.47.69l8.61 4.23a2 2 0 001.38.98l-8.61-4.23a2 2 0 00-.47-.69l8.57-4.25zm-10.46 1.3a12 12 0 00-4.24 1.12l-3.36 2.25a2 2 0 11-1.76-1.27l3.36-2.25zm-1.3-4.67a17 17 0 11-2.83 1.06l-3.5 2a2 2 0 11-1.45-.9l3.5-2zm3.61 9a2 2 0 114.31 0l-4.31-2zm-2.04.08a2 2 0 01-1.82 1.18l3.63 2.04a2 2 0 111.3 0l3.63-2.04a2 2 0 01-1.82-1.18h-4.34z"></path></svg>
                            </div>
                            <h5 class="card-title">{{ __('Easy Management') }}</h5>
                            <p class="card-text text-muted">
                                {{ __('Control your VMs with our intuitive dashboard and powerful API.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing Section -->
    <div class="py-5 bg-light">
        <div class="container">
            <div class="mb-5 text-center">
                <h2 class="fw-bold">{{ __('Simple, Transparent Pricing') }}</h2>
                <p class="text-muted">{{ __('Choose the plan that best fits your needs') }}</p>
            </div>
            <div class="row g-4 justify-content-center">
                @foreach($offers as $offer)
                <div class="col-md-3">
                    <div class="border-0 shadow-sm card h-100">
                        <div class="p-4 card-body">
                            <div class="text-center">
                                <h5 class="mb-4 card-title fw-bold">{{ $offer->name }}</h5>
                                <div class="mb-4 display-6 fw-bold">
                                    ${{ number_format($offer->price_per_hour, 1) }}
                                    <span class="text-muted fs-6">/hour</span>
                                </div>
                                <ul class="mb-4 list-unstyled">
                                    <li class="mb-3">
                                        <i class="bi bi-cpu text-primary me-2"></i>
                                        {{ $offer->cpu_count }} vCPUs
                                    </li>
                                    <li class="mb-3">
                                        <i class="bi bi-memory text-primary me-2"></i>
                                        {{ $offer->memory_size_mib }} MiB RAM
                                    </li>
                                    <li class="mb-3">
                                        <i class="bi bi-hdd text-primary me-2"></i>
                                        {{ $offer->disk_size_gb }} GB Storage
                                    </li>
                                </ul>
                                @auth
                                    <a href="{{ route('virtual-machines.create', ['offer' => $offer->id]) }}"
                                       class="btn btn-primary btn-lg w-100">
                                        {{ __('Deploy Now') }}
                                    </a>
                                @else
                                    <a href="{{ route('register') }}"
                                       class="btn btn-primary btn-lg w-100">
                                        {{ __('Get Started') }}
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <style>
        .feature-icon {
            width: 64px;
            height: 64px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</x-app-layout>
