<x-app-layout>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="border-0 shadow-sm card">
                    <div class="card-header bg-transparent border-0">
                        <h3 class="mb-0 h5">{{ __('Create Virtual Machine') }}</h3>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('virtual-machines.store') }}" class="needs-validation" novalidate>
                            @csrf

                            <!-- Nom de la VM -->
                            <div class="mb-4">
                                <label for="name" class="form-label">VM Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" name="name" value="{{ old('name') }}" required autofocus>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Mot de passe root -->
                            <div class="mb-4">
                                <label for="password" class="form-label">Root Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" required
                                    placeholder="Minimum 8 characters">
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="mt-1 form-text">
                                    This password will be used to access your VM as root user.
                                    Make sure to choose a strong password.
                                </div>
                            </div>

                            <!-- Sélection de l'offre -->
                            <div class="mb-4">
                                <label for="vm_offer_id" class="form-label">VM Offer</label>
                                <select id="vm_offer_id" name="vm_offer_id" 
                                    class="form-select @error('vm_offer_id') is-invalid @enderror" required>
                                    <option value="">Select an offer</option>
                                    @foreach ($offers as $offer)
                                    <option value="{{ $offer->id }}" 
                                        @if(($selectedOffer && $selectedOffer->id === $offer->id) || old('vm_offer_id') == $offer->id) selected @endif
                                        data-cpu="{{ $offer->cpu_count }}"
                                        data-ram="{{ $offer->memory_size_mib }}"
                                        data-storage="{{ $offer->disk_size_gb }}"
                                        data-price="{{ number_format($offer->price_per_hour, 1) }}">
                                        {{ $offer->name }} - {{ $offer->cpu_count }} vCPUs, 
                                        {{ $offer->memory_size_mib }}MiB RAM, 
                                        {{ $offer->disk_size_gb }}GB - 
                                        ${{ number_format($offer->price_per_hour, 1) }}/hour
                                    </option>
                                    @endforeach
                                </select>
                                @error('vm_offer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Sélection de l'image système -->
                            <div class="mb-4">
                                <label for="system_image_id" class="form-label">System Image</label>
                                <select id="system_image_id" name="system_image_id" 
                                    class="form-select @error('system_image_id') is-invalid @enderror" required>
                                    <option value="">Select a system image</option>
                                    @foreach ($systemImages as $image)
                                    <option value="{{ $image->id }}" 
                                        @if(old('system_image_id') == $image->id) selected @endif>
                                        {{ $image->name }} - {{ $image->os_type }} {{ $image->version }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('system_image_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Résumé de la configuration -->
                            <div class="p-4 mb-4 bg-light rounded">
                                <h4 class="mb-3 h6">Configuration Summary</h4>
                                <div id="config-summary" class="text-muted">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>Selected Offer:</strong> <span id="summary-offer">-</span></p>
                                            <p class="mb-2"><strong>vCPUs:</strong> <span id="summary-cpu">-</span></p>
                                            <p class="mb-2"><strong>Memory:</strong> <span id="summary-ram">-</span> MiB</p>
                                            <p class="mb-2"><strong>Storage:</strong> <span id="summary-storage">-</span> GB</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>System:</strong> <span id="summary-system">-</span></p>
                                            <p class="mb-2"><strong>Price:</strong> $<span id="summary-price">-</span>/hour</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-light" onclick="window.history.back()">
                                    {{ __('Cancel') }}
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Create Virtual Machine') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateConfigSummary() {
            const offerSelect = document.getElementById('vm_offer_id');
            const imageSelect = document.getElementById('system_image_id');
            const selectedOffer = offerSelect.options[offerSelect.selectedIndex];
            const selectedImage = imageSelect.options[imageSelect.selectedIndex];

            // Update offer details
            document.getElementById('summary-offer').textContent = selectedOffer.value ? selectedOffer.text.split(' - ')[0] : '-';
            document.getElementById('summary-cpu').textContent = selectedOffer.value ? selectedOffer.dataset.cpu : '-';
            document.getElementById('summary-ram').textContent = selectedOffer.value ? selectedOffer.dataset.ram : '-';
            document.getElementById('summary-storage').textContent = selectedOffer.value ? selectedOffer.dataset.storage : '-';
            document.getElementById('summary-price').textContent = selectedOffer.value ? selectedOffer.dataset.price : '-';

            // Update system image
            document.getElementById('summary-system').textContent = selectedImage.value ? selectedImage.text : '-';
        }

        // Update summary on page load and when selections change
        document.addEventListener('DOMContentLoaded', updateConfigSummary);
        document.getElementById('vm_offer_id').addEventListener('change', updateConfigSummary);
        document.getElementById('system_image_id').addEventListener('change', updateConfigSummary);

        // Bootstrap form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
    @endpush
</x-app-layout>
