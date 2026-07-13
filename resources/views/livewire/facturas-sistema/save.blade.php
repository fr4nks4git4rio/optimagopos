@section('title', $factura_id ? __('site.invoices.save_invoice.edit_invoice') :
    __('site.invoices.save_invoice.create_invoice'))

    <div class="container-fluid py-4 position-relative">

        <div wire:loading.delay.longer>
            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center"
                style="background: rgba(255,255,255,0.7); z-index: 1050; min-height: 400px;">
                <div class="text-center">
                    <div class="spinner-border text-primary mb-2" role="status" style="width: 3rem; height: 3rem;"></div>
                    <p class="text-muted fw-bold small">{{ __('site.invoices.save_invoice.processing_data') }}...</p>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom pb-3 mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('admin.pre-facturas.index') }}"
                                class="text-decoration-none">{{ __('site.invoices.save_invoice.pre_invoices') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $factura_id ? __('site.invoices.save_invoice.edit') : __('site.invoices.save_invoice.new') }}
                        </li>
                    </ol>
                </nav>
                <h1 class="h2 fw-bold text-dark mb-0">@yield('title')</h1>
            </div>
            <div class="d-flex gap-2 mt-3 mt-md-0">
                <a href="{{ route('admin.pre-facturas.index') }}"
                    class="btn btn-outline-secondary d-inline-flex align-items-center">
                    <i class="bi bi-arrow-left me-1"></i> {{ __('site.invoices.save_invoice.back') }}
                </a>
                <button wire:click="saveFactura()"
                    class="btn btn-primary d-inline-flex align-items-center fw-bold shadow-sm">
                    <i class="bi bi-download me-1"></i> {{ __('site.invoices.save_invoice.save') }}
                </button>
                @if ($factura_id)
                    <button wire:click="showPdf()" class="btn btn-outline-primary d-inline-flex align-items-center">
                        <i class="bi bi-file-earmark-pdf me-1"></i> {{ __('site.common.download_pdf') }}
                    </button>
                    <button wire:click="timbrarFactura({{ $factura_id }})"
                        class="btn btn-success d-inline-flex align-items-center fw-bold shadow-sm">
                        <i class="bi bi-lightning-fill me-1"></i> {{ __('site.common.stamp') }}
                    </button>
                @endif
            </div>
        </div>

        <div wire:init="loadInitialData">
            <span style="display: none">{{ $this->form_with_errors }}</span>

            <div class="row g-4">

                <div class="col-xl-8">

                    <div class="row g-2">
                        <div class="col-md-6 col-12">
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-body p-4">
                                    <h5 class="card-title fw-bold text-dark mb-3 d-flex align-items-center">
                                        <i class="bi bi-person-badge text-primary me-2"></i>
                                        {{ __('site.invoices.save_invoice.client_currency_selection') }}
                                    </h5>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <x-select2-ajax label="{{ __('site.invoices.save_invoice.client') }} *"
                                                placeholder="{{ __('site.invoices.save_invoice.search_client') }}..."
                                                class="form-control" url="{{ route('clientes.load-clientes') }}"
                                                model="cliente_id" />
                                        </div>
                                        <div class="col-md-6">
                                            <label
                                                class="fw-semibold small text-muted">{{ __('site.invoices.save_invoice.currency') }}
                                                *</label>
                                            <select class="form-select @error('moneda') is-invalid @enderror"
                                                wire:model="moneda" id="id_moneda">
                                                @foreach ($monedas as $moneda)
                                                    <option value="{{ $moneda }}">{{ $moneda }}</option>
                                                @endforeach
                                            </select>
                                            @error('moneda')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label
                                                class="fw-semibold small text-muted">{{ __('site.invoices.save_invoice.exchange_rate') }}</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light"><i
                                                        class="bi bi-currency-exchange"></i></span>
                                                <input type="number" step="0.0001"
                                                    class="form-control @error('tipo_cambio') is-invalid @enderror" disabled
                                                    wire:model="tipo_cambio">
                                            </div>
                                            @error('tipo_cambio')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="mt-3">
                                <label class="form-label fw-bold text-secondary text-uppercase tracking-wider small mb-2">
                                    <i class="bi bi-arrow-repeat me-1"></i>
                                    {{ __('site.invoices.save_invoice.available_subscriptions') }}
                                </label>

                                <div class="card shadow-sm border rounded-3 overflow-hidden">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light text-uppercase tracking-wider small"
                                                style="font-size: 0.75rem;">
                                                <tr>
                                                    <th class="text-center" style="width: 50px;"></th>
                                                    <th style="width: 100px;">{{ __('site.invoices.save_invoice.number') }}
                                                    </th>
                                                    <th>{{ __('site.invoices.save_invoice.plan') }}</th>
                                                    <th class="text-center" style="width: 150px;">
                                                        {{ __('site.invoices.save_invoice.status') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody style="font-size: 0.9rem;">

                                                @if (!$this->client_is_selected)
                                                    <tr>
                                                        <td colspan="4" class="py-4">
                                                            <div class="text-center text-muted">
                                                                <i
                                                                    class="bi bi-person-x fs-4 d-block mb-1 text-secondary"></i>
                                                                <span>{{ __('site.invoices.save_invoice.select_client_to_load_subs') }}</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @else
                                                    @if (count($suscripciones) > 0)
                                                        @foreach ($suscripciones as $index => $suscripcion)
                                                            <tr x-data="{
                                                                seleccionada: {{ $suscripcion['seleccionada'] ? 'true' : 'false' }},
                                                                id_suscripcion: '{{ $suscripcion['id'] }}',
                                                                checkSuscripcion(index) {
                                                                    this.seleccionada = !this.seleccionada;
                                                                    setTimeout(() => {
                                                                        @this.checkSuscripcion(index);
                                                                    }, 100);
                                                                }
                                                            }" x-init="Livewire.on('unselect-suscripcion', id => {
                                                                if (id_suscripcion == id) {
                                                                    seleccionada = false;
                                                                }
                                                            })"
                                                                :class="seleccionada ? 'table-primary-subtle' : ''"
                                                                style="transition: background-color 0.2s ease;">

                                                                <td class="text-center ps-3">
                                                                    <div
                                                                        class="form-check d-flex justify-content-center align-items-center m-0">
                                                                        <input type="checkbox"
                                                                            class="form-check-input cursor-pointer"
                                                                            style="width: 1.15rem; height: 1.15rem;"
                                                                            x-bind:checked="seleccionada"
                                                                            @click="checkSuscripcion('{{ $index }}')">
                                                                    </div>
                                                                </td>

                                                                <td class="fw-semibold text-secondary font-monospace">
                                                                    #{{ $suscripcion['id'] }}
                                                                </td>

                                                                <td>
                                                                    <div class="fw-bold text-dark">
                                                                        {{ $suscripcion['paquete'] }}</div>
                                                                </td>

                                                                <td class="text-center pe-3">
                                                                    @switch(strtolower($suscripcion['estado']))
                                                                        @case('activa')
                                                                            <span
                                                                                class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-bold">{{ __('site.statuses.subscriptions.' . $suscripcion['estado']) }}</span>
                                                                        @break

                                                                        @case('pendiente')
                                                                            <span
                                                                                class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 small fw-bold">{{ __('site.statuses.subscriptions.' . $suscripcion['estado']) }}</span>
                                                                        @break

                                                                        @default
                                                                            <span
                                                                                class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 small fw-bold">{{ __('site.statuses.subscriptions.' . $suscripcion['estado']) }}</span>
                                                                    @endswitch
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <tr>
                                                            <td colspan="4" class="py-4">
                                                                <div class="text-center text-muted">
                                                                    <i
                                                                        class="bi bi-folder-x fs-4 d-block mb-1 text-secondary"></i>
                                                                    <span>{{ __('site.invoices.save_invoice.client_without_subs') }}</span>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endif

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4">
                            <h5 class="card-title fw-bold text-dark mb-3 d-flex align-items-center">
                                <i class="bi bi-file-earmark-ruled text-primary me-2"></i>
                                {{ __('site.invoices.save_invoice.fiscal_data') }}
                            </h5>

                            <div class="row p-3 bg-light rounded border mx-0 mb-4 g-2">
                                <div class="col-md-5">
                                    <span class="d-block text-muted small fw-semibold text-uppercase"
                                        style="font-size:0.7rem;">{{ __('site.invoices.save_invoice.receiver') }}</span>
                                    <span
                                        class="text-dark fw-bold">{{ $this->razon_social_receptor ?: __('site.invoices.save_invoice.not_selected') }}</span>
                                </div>
                                <div class="col-md-4">
                                    <span class="d-block text-muted small fw-semibold text-uppercase"
                                        style="font-size:0.7rem;">{{ __('site.invoices.save_invoice.rfc') }}</span>
                                    <code class="text-primary fw-bold fs-6">{{ $this->rfc_receptor ?: 'N/A' }}</code>
                                </div>
                                <div class="col-md-3">
                                    <span class="d-block text-muted small fw-semibold text-uppercase"
                                        style="font-size:0.7rem;">{{ __('site.invoices.save_invoice.postal_code') }}</span>
                                    <span
                                        class="text-dark fw-bold">{{ $lugar_expedicion ?: __('site.common.n/a') }}</span>
                                    @error('lugar_expedicion')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <x-select2 label="{{ __('site.invoices.save_invoice.serie') }}"
                                        placeholder="{{ __('site.common.select') }}..." class="form-control"
                                        :options="$series" model="serie_id" />
                                </div>
                                <div class="col-md-4">
                                    <label
                                        class="fw-semibold small text-muted">{{ __('site.invoices.save_invoice.issue_date') }}</label>
                                    <input type="text" class="form-control" wire:model="fecha_emision_str" disabled>
                                </div>
                                <div class="col-md-4">
                                    <x-select2 label="{{ __('site.invoices.save_invoice.cfdi') }}"
                                        placeholder="{{ __('site.common.select') }}..." class="form-control"
                                        :options="$usosCfdi" model="cfdi_id" />
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <x-select2 label="{{ __('site.invoices.save_invoice.payment_form') }}"
                                        placeholder="{{ __('site.common.select') }}..." class="form-control"
                                        :options="$formasPago" model="forma_pago_id" />
                                </div>
                                <div class="col-md-4">
                                    <x-select2 label="{{ __('site.invoices.save_invoice.payment_method') }}"
                                        placeholder="{{ __('site.common.select') }}..." class="form-control"
                                        :options="$metodosPago" model="metodo_pago_id" />
                                </div>
                                <div class="col-md-4">
                                    <x-select2 label="{{ __('site.invoices.save_invoice.receipt_type') }}"
                                        placeholder="{{ __('site.common.select') }}..." class="form-control"
                                        :options="$tiposComprobante" model="tipo_comprobante_id" />
                                </div>
                            </div>

                            @if ($this->client_is_publico_general)
                                <div class="row g-3 mt-2 p-3 bg-warning-subtle rounded border border-warning-subtle mx-0">
                                    <div class="col-md-4 mt-0">
                                        <x-select2 label="{{ __('site.invoices.save_invoice.periodicity') }}"
                                            placeholder="{{ __('site.common.select') }}..." class="form-control"
                                            :options="$periodicidades" model="periodicidad_id" />
                                    </div>
                                    <div class="col-md-4 mt-0">
                                        <x-select2 label="{{ __('site.invoices.save_invoice.period_month') }}"
                                            placeholder="{{ __('site.common.select') }}..." class="form-control"
                                            :options="$meses" model="mes_id" />
                                    </div>
                                    <div class="col-md-4 mt-0">
                                        <label class=form-label fw-semibold small
                                            text-muted">{{ __('site.invoices.save_invoice.year') }}</label>
                                        <select class="form-select @error('anio') is-invalid @enderror" wire:model="anio">
                                            @foreach ($anios as $a)
                                                <option value="{{ $a }}">{{ $a }}</option>
                                            @endforeach
                                        </select>
                                        @error('anio')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4">
                        <div
                            class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h5 class="card-title fw-bold text-dark mb-0 d-flex align-items-center">
                                <i class="bi bi-list-stars text-primary me-2"></i>
                                {{ __('site.invoices.save_invoice.invoice_concepts') }}
                            </h5>
                            <button type="button"
                                class="btn btn-success btn-sm d-inline-flex align-items-center fw-semibold"
                                wire:click="showConceptoFacturaModal">
                                <i class="bi bi-plus-circle me-1"></i> {{ __('site.invoices.save_invoice.add_concept') }}
                            </button>
                        </div>
                        <div class="card-body p-0">
                            @error('factura_conceptos')
                                <div class="alert alert-danger rounded-0 border-0 m-0 py-2 small shadow-sm">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    {{ __('site.invoices.save_invoice.error_in_concepts') }}:
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 text-nowrap">
                                    <thead class="table-light text-uppercase text-muted"
                                        style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                        <tr>
                                            <th class="text-center px-4">{{ __('site.invoices.save_invoice.quantity') }}
                                            </th>
                                            <th>{{ __('site.invoices.save_invoice.prod_serv_code') }}</th>
                                            <th class="text-center">{{ __('site.invoices.save_invoice.unitity_code') }}
                                            </th>
                                            <th>{{ __('site.invoices.save_invoice.description') }}</th>
                                            <th class="text-end">{{ __('site.invoices.save_invoice.unit_value') }}</th>
                                            <th class="text-end px-4">{{ __('site.invoices.save_invoice.import') }}</th>
                                            <th class="text-center">{{ __('site.common.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        @if ($factura_conceptos && count($factura_conceptos) > 0)
                                            @foreach ($factura_conceptos as $index => $concept)
                                                <tr>
                                                    <td class="text-center fw-bold px-4 text-dark">
                                                        {{ $concept['cantidad'] }}
                                                        @error("factura_conceptos.{$index}.cantidad")
                                                            <div class="text-danger x-small d-block fw-normal">
                                                                {{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge bg-light text-secondary border fw-mono fs-7">{{ $concept['clave_prod_serv'] ? $concept['clave_prod_serv']['codigo'] : '---' }}</span>
                                                        @error("factura_conceptos.{$index}.clave_prod_serv_id")
                                                            <div class="text-danger x-small d-block">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td class="text-center text-muted fw-semibold">
                                                        {{ $concept['clave_unidad'] ? $concept['clave_unidad']['codigo'] : '---' }}
                                                        @error("factura_conceptos.{$index}.clave_unidad_id")
                                                            <div class="text-danger x-small d-block">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <div class="text-wrap text-dark"
                                                            style="max-width: 260px; font-size: 0.85rem;">
                                                            {{ $concept['descripcion'] }}
                                                        </div>
                                                        <small class="text-muted d-block fs-7">Objeto Imp:
                                                            {{ $concept['objeto_impuesto'] ? Str::limit($concept['objeto_impuesto']['nombre'], 20) : 'No objeto' }}</small>
                                                    </td>
                                                    <td class="text-end font-monospace text-secondary">
                                                        ${{ number_format($concept['precio_unitario'], 2) }}</td>
                                                    <td class="text-end font-monospace fw-bold text-dark px-4">
                                                        ${{ number_format($concept['precio_unitario'] * $concept['cantidad'], 2) }}
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" class="btn btn-outline-secondary"
                                                                wire:click="showConceptoFacturaModal({{ $index }})"
                                                                title="{{ __('site.common.edit') }}">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-outline-danger"
                                                                wire:click="eliminarConceptoFactura({{ $index }})"
                                                                title="{{ __('site.invoices.save_invoice.delete') }}">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="7" class="text-center py-5 text-muted">
                                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                                    {{ __('site.invoices.save_invoice.no_invoice_concepts') }}
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="sticky-top" style="top: 24px; z-index: 10;">

                        <div class="card shadow-sm border-0 mb-4 bg-dark text-white">
                            <div class="card-header border-bottom border-secondary bg-transparent py-3">
                                <h6 class="mb-0 text-uppercase fw-bold text-muted-light"
                                    style="font-size:0.75rem; letter-spacing:1px;">
                                    {{ __('site.invoices.save_invoice.economic_summary') }}</h6>
                            </div>
                            <div class="card-body p-4">
                                @if ($factura_conceptos && count($factura_conceptos) > 0)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span
                                            class="text-muted-light small">{{ __('site.invoices.save_invoice.subtotal') }}</span>
                                        <span
                                            class="font-monospace fw-semibold">${{ number_format($this->subtotal, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span
                                            class="text-muted-light small">{{ __('site.invoices.save_invoice.iva') }}</span>
                                        <span class="font-monospace text-warning-subtle">+
                                            ${{ number_format($this->iva, 2) }}</span>
                                    </div>
                                    <hr class="border-secondary my-3">
                                    <div class="d-flex justify-content-between align-items-center mb-0">
                                        <span class="fw-bold fs-5">{{ __('site.invoices.save_invoice.total') }}</span>
                                        <span
                                            class="font-monospace fw-bold fs-3 text-success">${{ number_format($this->total, 2) }}
                                            <small
                                                class="fs-6 text-muted-light font-sans fw-normal">{{ $moneda }}</small></span>
                                    </div>
                                @else
                                    <div class="text-center py-4 text-muted-light small">
                                        {{ __('site.invoices.save_invoice.calculating_economic_summary') }}...
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="card shadow-sm border-0 mb-4">
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <label
                                        class="fw-semibold small text-muted">{{ __('site.invoices.save_invoice.quantity_in_words') }}</label>
                                    <textarea class="form-control text-dark small bg-light" wire:model="cantidad_letras" rows="2" disabled></textarea>
                                </div>

                                <div class="mb-0">
                                    <x-textarea label="{{ __('site.invoices.save_invoice.comments') }}"
                                        model="comentarios"
                                        placeholder="{{ __('site.invoices.save_invoice.comments_placeholder') }}..."
                                        rows="3" />
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="chkAsociar"
                                wire:model.lazy="con_facturas_relacionadas">
                            <label class="form-check-label fw-semibold text-dark small" style="cursor:pointer;"
                                for="chkAsociar">
                                {{ __('site.invoices.save_invoice.associate_invoices') }}
                            </label>
                        </div>
                    </div>
                    @if ($con_facturas_relacionadas)
                        <div class="card shadow-sm border-0 mt-4 animate__animated animate__fadeIn">
                            <div class="card-body p-4">
                                <h5 class="card-title fw-bold text-dark mb-3 d-flex align-items-center">
                                    <i class="bi bi-link-45deg text-primary me-1"></i>
                                    {{ __('site.invoices.save_invoice.invoices_associated') }}
                                </h5>
                                <div class="mb-4">
                                    <x-select2 label="{{ __('site.invoices.save_invoice.invoice_relation_type') }} *"
                                        placeholder="{{ __('site.common.select') }}..." :options="$tipoRelacionesFacturas"
                                        model="tipo_relacion_factura_id" class="form-control" />
                                </div>

                                <h6 class="fw-bold text-secondary small text-uppercase mb-2"
                                    style="letter-spacing:0.5px;">
                                    {{ __('site.invoices.save_invoice.stamped_inoices_available') }}
                                </h6>
                                <div class="table-responsive border rounded">
                                    <table class="table table-striped table-hover align-middle mb-0 text-nowrap">
                                        <thead class="table-light small text-uppercase text-muted">
                                            <tr>
                                                <th class="text-center" width="60">Sel.</th>
                                                <th>{{ __('site.invoices.save_invoice.issue_date') }}</th>
                                                <th>{{ __('site.invoices.save_invoice.internal_folio') }}</th>
                                                <th>{{ __('site.invoices.save_invoice.type') }}</th>
                                                <th>{{ __('site.invoices.save_invoice.status') }}</th>
                                                <th class="text-end">{{ __('site.invoices.save_invoice.subtotal') }}</th>
                                                <th class="text-end px-4">{{ __('site.invoices.save_invoice.total') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (count($facturasTimbradas) > 0)
                                                @foreach ($facturasTimbradas as $index => $factura)
                                                    <tr x-data="{ seleccionada: {{ $factura['seleccionada'] ? 'true' : 'false' }} }">
                                                        <td class="text-center">
                                                            <input type="checkbox" class="form-check-input"
                                                                x-model="seleccionada"
                                                                @change="@this.checkFacturaTimbrada('{{ $index }}')">
                                                        </td>
                                                        <td>{{ $factura['fecha_emision_str'] }}</td>
                                                        <td class="fw-bold text-dark">{{ $factura['folio_interno'] }}
                                                        </td>
                                                        <td>
                                                            @if ($factura['es_nota_credito'])
                                                                <span
                                                                    class="badge bg-info-subtle text-info border border-info-subtle">NOT.CRE.
                                                                </span>
                                                            @elseif($factura['es_complemento'])
                                                                <span
                                                                    class="badge bg-primary-subtle text-primary border border-primary-subtle">COMP.</span>
                                                            @else
                                                                <span
                                                                    class="badge bg-success-subtle text-success border border-success-subtle">FACT.</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span
                                                                class="badge {{ $factura['estado'] == 'TIMBRADA' ? 'bg-success' : 'bg-primary' }}">{{ __('site.statuses.invoices.' . $factura['estado']) }}</span>
                                                        </td>
                                                        <td class="text-end font-monospace">
                                                            ${{ number_format($factura['subtotal'], 2) }}</td>
                                                        <td class="text-end font-monospace fw-bold text-dark px-4">
                                                            ${{ number_format($factura['total'], 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="7" class="text-center py-4 text-muted small">
                                                    {{ __('site.invoices.save_invoice.not_invoices_found') }}
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @livewire('facturas-sistema.save-factura-concepto')

        <div class="modal fade {{ $iframeContainerClass ? 'show d-block bg-dark bg-opacity-50' : '' }}" tabindex="-1"
            role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content shadow-lg border-0">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold text-dark"><i
                                class="bi bi-file-pdf text-danger me-2"></i>Previsualización del Comprobante Fiscal</h5>
                        <button type="button" class="btn-close" wire:click="$set('iframeContainerClass', '')"></button>
                    </div>
                    <div class="modal-body p-0 bg-secondary">
                        @if ($iframeSrc)
                            <iframe src="{{ $iframeSrc }}" frameborder="0" class="w-100"
                                style="height: 75vh; display: block;"></iframe>
                        @else
                            <div class="text-center py-5 text-white">
                                <div class="spinner-border text-light mb-2" role="status"></div>
                                <p class="mb-0">Renderizando documento PDF...</p>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary px-4 fw-semibold"
                            wire:click="$set('iframeContainerClass', '')">Cerrar Visor</button>
                    </div>
                </div>
            </div>
        </div>

        @livewire('facturas-sistema.consecutivo-factura')
    </div>
