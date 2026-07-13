<div class="modal fade {{ $show ? 'show d-block bg-dark bg-opacity-50' : '' }}" id="{{ $modal_id }}" tabindex="-1"
    role="dialog" aria-labelledby="modalTitle-{{ $modal_id }}" aria-hidden="{{ $show ? 'false' : 'true' }}">

    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">

            <div class="modal-header bg-light border-bottom-0 py-3">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center" id="modalTitle-{{ $modal_id }}">
                    <i class="bi bi-box-seam text-primary me-2"></i>
                    {{ $title ?: __('site.invoices.invoice_concept.manage_concept') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                    wire:click="$set('show', '')"></button>
            </div>

            <div class="modal-body p-4">

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="position-relative">
                            <x-input type="number" model="cantidad"
                                label="{{ __('site.invoices.invoice_concept.quantity') }} *" placeholder="0.00"
                                class="form-control" />
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="position-relative">
                            <x-input model="precio_unitario"
                                label="{{ __('site.invoices.invoice_concept.unit_price') }} *" placeholder="0.00"
                                class="form-control" />
                        </div>
                    </div>

                    <div class="col-md-5">
                        <x-select2-component-modals parent="{{ $modal_id }}" class="form-control"
                            label="{{ __('site.invoices.invoice_concept.tax_object') }} *" :options="$objetosImpuestos"
                            model="objeto_impuesto_id" :dynamic="true" />
                    </div>
                </div>

                <div class="row g-3 mb-4 p-3 bg-light rounded border mx-0">
                    <div class="col-md-12 mt-0 mb-2">
                        <span class="text-uppercase text-muted fw-bold tracking-wider"
                            style="font-size: 0.7rem; letter-spacing: 0.5px;">
                            {{ __('site.invoices.invoice_concept.required_clasification') }}
                        </span>
                    </div>
                    <div class="col-md-7 mt-0">
                        <x-select2-ajax-component-modals
                            label="{{ __('site.invoices.invoice_concept.prod_serv_code') }}"
                            placeholder="{{ __('site.common.select') }}..." class="form-control"
                            url="{{ route('claves-prod-servs.load-claves-prod-servs') }}" parent="{{ $modal_id }}"
                            model="clave_prod_serv_id" />
                    </div>
                    <div class="col-md-5 mt-0">
                        <x-select2-component-modals parent="{{ $modal_id }}" class="form-control"
                            placeholder="{{ __('site.common.select') }}..."
                            label="{{ __('site.invoices.invoice_concept.code_unit') }}" :options="$claveUnidades"
                            model="clave_unidad_id" :dynamic="true" />
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <x-textarea model="descripcion" label="{{ __('site.invoices.invoice_concept.concept') }}"
                            placeholder="{{ __('site.invoices.invoice_concept.concept_placeholder') }}..."
                            rows="3" />
                    </div>
                </div>

                @if (is_numeric($cantidad) && is_numeric($precio_unitario))
                    <div
                        class="d-flex justify-content-end align-items-center mt-3 p-2 bg-success-subtle rounded border border-success-subtle px-3">
                        <span class="text-success small fw-semibold me-2"><i class="bi bi-calculator me-1"></i>
                            {{ __('site.invoices.invoice_concept.calculated_subtotal') }}:
                        </span>
                        <span
                            class="font-monospace fw-bold text-success-emphasis">${{ number_format($cantidad * $precio_unitario, 2) }}</span>
                    </div>
                @endif

            </div>

            <div class="modal-footer bg-light border-top-0 py-3">
                <button type="button" class="btn btn-outline-secondary px-4 fw-semibold" data-bs-dismiss="modal"
                    wire:click="$set('show', '')">
                    {{__('site.common.close') }}
                </button>
                <button type="button" wire:click="guardar()"
                    class="btn btn-success px-4 fw-bold shadow-sm d-inline-flex align-items-center">
                    <i class="bi bi-check-circle me-1"></i> {{__('site.invoices.invoice_concept.add_concept') }}
                </button>
            </div>

        </div>
    </div>
</div>
