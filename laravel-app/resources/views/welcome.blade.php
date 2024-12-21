<x-app-layout>
    <!-- Hero Section -->
    <div class="bg-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-3">{{ __('Deploy Virtual Machines in Seconds') }}</h1>
                    <p class="lead mb-4">{{ __('Experience lightning-fast VM deployment with our Firecracker-powered platform. Get started in minutes with secure, isolated, and high-performance virtual machines.') }}</p>
                    <div class="d-grid gap-2 d-md-flex">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-light btn-lg px-4">
                                <i class="bi bi-speedometer2 me-2"></i>{{ __('Go to Dashboard') }}
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="btn btn-light btn-lg px-4">
                                <i class="bi bi-person-plus me-2"></i>{{ __('Get Started') }}
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-4">
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
            <div class="text-center mb-5">
                <h2 class="fw-bold">{{ __('Why Choose Our Platform?') }}</h2>
                <p class="text-muted">{{ __('Discover the benefits of our modern VM management solution') }}</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-primary bg-gradient text-white rounded-circle mb-3">
                                <i class="bi bi-lightning-charge fs-2"></i>
                            </div>
                            <h5 class="card-title">{{ __('Lightning Fast') }}</h5>
                            <p class="card-text text-muted">
                                {{ __('Launch new VMs in milliseconds with Firecracker\'s innovative MicroVM technology.') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-primary bg-gradient text-white rounded-circle mb-3">
                                <i class="bi bi-shield-check fs-2"></i>
                            </div>
                            <h5 class="card-title">{{ __('Secure by Design') }}</h5>
                            <p class="card-text text-muted">
                                {{ __('Benefit from strong isolation and security with our virtualization technology.') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-primary bg-gradient text-white rounded-circle mb-3">
                                <i class="bi bi-gear fs-2"></i>
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
    <div class="bg-light py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">{{ __('Simple, Transparent Pricing') }}</h2>
                <p class="text-muted">{{ __('Choose the plan that best fits your needs') }}</p>
            </div>
            <div class="row g-4 justify-content-center">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="text-center">
                                <h5 class="fw-bold">{{ __('Basic') }}</h5>
                                <div class="display-4 my-3">$10<small class="fs-6">/mo</small></div>
                            </div>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>2 Virtual Machines</li>
                                <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>2 vCPUs per VM</li>
                                <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>2GB RAM per VM</li>
                                <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>20GB Storage per VM</li>
                            </ul>
                            <div class="d-grid">
                                <a href="{{ route('register') }}" class="btn btn-outline-primary">
                                    {{ __('Get Started') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow">
                        <div class="card-body p-4">
                            <div class="text-center">
                                <div class="badge bg-primary mb-2">{{ __('Most Popular') }}</div>
                                <h5 class="fw-bold">{{ __('Pro') }}</h5>
                                <div class="display-4 my-3">$25<small class="fs-6">/mo</small></div>
                            </div>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>5 Virtual Machines</li>
                                <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>4 vCPUs per VM</li>
                                <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>4GB RAM per VM</li>
                                <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i>50GB Storage per VM</li>
                            </ul>
                            <div class="d-grid">
                                <a href="{{ route('register') }}" class="btn btn-primary">
                                    {{ __('Get Started') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
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
