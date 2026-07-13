<x-modal form-action="save">
    <x-slot:title>
        {{ __('site.panel_pac.title') }}
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init" class="row g-4">
            <div class="col-12" x-data="{
                rfc: '{{ $rfc }}',
                portalPac: '{{ $portal_pac }}',
                timbres: '{{ __('site.panel_pac.getting_data') }}...',
                loading: true,
                statusClass: 'text-muted',

                init() {
                    window.livewire.on('cfdi_timbrado_productivo_updated', (value) => {
                        this.$nextTick(() => {
                            this.buscarTimbres();
                        });
                    });

                    this.buscarTimbres();
                },

                async buscarTimbres() {
                    this.loading = true;
                    this.timbres = '{{ __('site.panel_pac.getting_data') }}...';
                    this.statusClass = 'text-muted';
                    try {
                        let response = await fetch('/admin/obtener-timbres-disponibles/' + this.rfc);
                        let data = await response.json();

                        this.loading = false;
                        if (data.success) {
                            this.timbres = data.disponibles;
                            this.statusClass = 'text-success fw-bold fs-5';
                        } else {
                            this.timbres = data.message || '{{ __('site.panel_pac.getting_data_error') }}';
                            this.statusClass = 'text-danger small';
                            if (!data.message) {
                                $emit('show-toast', { message: '{{ __('site.panel_pac.error') }}', type: 'danger' });
                            }
                        }
                    } catch (error) {
                        this.loading = false;
                        this.timbres = '{{__('site.panel_pac.connection_error')}}';
                        this.statusClass = 'text-danger small';
                        $emit('show-toast', { message: error.message, type: 'danger' });
                    }
                }
            }">

                <div class="row g-4">

                    <div class="col-md-12">
                        <div class="card h-100 shadow-sm border-0 rounded-3">
                            <div
                                class="card-body p-4 text-center d-flex flex-column justify-content-center align-items-center">
                                <div class="bg-primary-subtle text-primary rounded-circle p-3 mb-3">
                                    <i class="bi bi-toggle-on fs-4 p-1"></i>
                                </div>
                                <h6 class="text-uppercase text-muted fw-bold fs-8 tracking-wider mb-3">{{__('site.panel_pac.stamping_method')}}
                                </h6>
                                <div class="py-2 w-100 bg-light rounded-3 border justify-content-center d-flex">
                                    <div>
                                        <label for="" class="form-label">{{ __('site.panel_pac.production_method') }}:</label>
                                        <x-toggle-button :lazy="true" model="cfdi_timbrado_productivo" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="card h-100 shadow-sm border-0 rounded-3">
                            <div
                                class="card-body p-4 text-center d-flex flex-column justify-content-center align-items-center">
                                <div class="rounded-circle p-3 mb-3"
                                    :class="loading ? 'bg-light text-muted' : 'bg-success-subtle text-success'">
                                    <template x-if="loading">
                                        <div class="spinner-border spinner-border-sm" role="status"></div>
                                    </template>
                                    <template x-if="!loading">
                                        <i class="bi bi-lightning-charge-fill fs-4 p-1"></i>
                                    </template>
                                </div>
                                <h6 class="text-uppercase text-muted fw-bold fs-8 tracking-wider mb-2">
                                    {{__('site.panel_pac.available_stamps')}}
                                </h6>

                                <div class="w-100">
                                    <input type="text"
                                        class="form-control text-center bg-light border-secondary-subtle"
                                        :class="statusClass" x-model="timbres" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="card h-100 shadow-sm border-0 rounded-3">
                            <div
                                class="card-body p-4 text-center d-flex flex-column justify-content-center align-items-center">
                                <div class="bg-danger-subtle text-danger rounded-circle p-3 mb-3">
                                    <i class="bi bi-globe fs-4 p-1"></i>
                                </div>
                                <h6 class="text-uppercase text-muted fw-bold fs-8 tracking-wider mb-3">{{ __('site.panel_pac.review_invoices') }}
                                </h6>
                                <a :href="portalPac" target="_blank"
                                    class="btn btn-outline-danger fw-bold w-100 py-2 rounded-3 transition-all">
                                    <i class="bi bi-box-arrow-up-right me-2"></i>{{ __('site.panel_pac.visit_panel_pac_site') }}
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">
            {{__('site.common.close')}}
        </button>
    </x-slot:buttons>
</x-modal>
