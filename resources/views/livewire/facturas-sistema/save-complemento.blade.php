@section('title', $complemento->id ? __('site.invoices.save_complement.edit_complement') :
    __('site.invoices.save_complement.create_complement'))

    <div class="container-fluid px-0">
        <div wire:loading.delay.longer>
            <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-40"
                style="z-index: 9999;">
                <div class="bg-white p-4 rounded-4 shadow-lg text-center">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <div class="small fw-bold text-secondary">{{ __('site.invoices.save_complement.processing_data') }}...
                    </div>
                </div>
            </div>
        </div>

        <div
            class="d-flex flex-column flex-md-row align-items-md-center justify-content-between pb-3 mb-4 border-bottom gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="{{ route('admin.pre-facturas.index') }}"
                                class="text-decoration-none">{{ __('site.invoices.save_complement.pre_invoices') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $complemento->id ? __('site.invoices.save_complement.edit') : __('site.invoices.save_complement.new') }}
                        </li>
                    </ol>
                </nav>
                <h1 class="h2 fw-bold text-dark mb-0">@yield('title')</h1>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.pre-facturas.index') }}" class="btn btn-light border fw-semibold px-3">
                    <i class="bi bi-arrow-left me-1"></i> {{ __('site.invoices.save_complement.back') }}
                </a>
                <button type="button" wire:click="saveComplemento()" class="btn btn-primary fw-bold px-3 shadow-sm">
                    <i class="bi bi-download me-1"></i> {{ __('site.invoices.save_complement.save') }}
                </button>
                @if ($complemento->exists)
                    <button type="button" wire:click="showPdf()" class="btn btn-outline-secondary fw-semibold px-3">
                        <i class="bi bi-file-pdf me-1"></i> {{ __('site.common.download_pdf') }}
                    </button>
                    <button type="button" wire:click="timbrarComplemento({{ $complemento->id }})"
                        class="btn btn-success fw-bold px-4 shadow-sm">
                        <i class="bi bi-shield-check me-1"></i> {{ __('site.common.stamp') }}
                    </button>
                @endif
            </div>
        </div>

        <div wire:init="loadInitialData" class="row g-4">

            <div class="col-12">
                <div class="card shadow-sm border rounded-3">
                    <div class="card-header bg-light py-3 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark text-uppercase tracking-wider small"><i
                                class="bi bi-person-lines-fill me-2 text-primary"></i>{{ __('site.invoices.save_complement.client_origin_selection') }}
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-xl-6 col-12">
                                <div class="position-relative mb-3">
                                    <x-select2-ajax label="{{ __('site.invoices.save_complement.client') }}"
                                        placeholder="{{ __('site.common.select') }}..." class="form-control"
                                        url="{{ route('clientes.load-clientes') }}" model="cliente_id" />
                                </div>

                                <div class="row g-3">
                                    <div class="col-sm-4">
                                        <x-select2-ajax label="{{ __('site.invoices.save_complement.serie') }}"
                                            placeholder="{{ __('site.common.select') }}..." class="form-control"
                                            url="{{ route('series.load-series') }}" model="serie_id" />
                                    </div>
                                    <div class="col-sm-4">
                                        <label
                                            class="text-muted small fw-bold">{{ __('site.invoices.save_complement.issue_date') }}</label>
                                        <input type="text"
                                            class="form-control form-control bg-light font-monospace text-secondary fw-semibold"
                                            wire:model="fecha_emision_str" disabled>
                                    </div>
                                    <div class="col-sm-4">
                                        <label
                                            class="text-muted small fw-bold">{{ __('site.invoices.save_complement.postal_code') }}</label>
                                        <input type="text"
                                            class="form-control form-control bg-light font-monospace text-secondary fw-semibold"
                                            value="{{ get_system_owner()->codigo_postal }}" disabled>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-6 col-12">
                                <label
                                    class="text-muted small fw-bold d-block mb-2">{{ __('site.invoices.save_complement.associate_available_invoices') }}</label>
                                <div class="border rounded-3 overflow-hidden">
                                    <div class="table-responsive" style="max-height: 180px; overflow-y: auto;">
                                        <table class="table table-sm table-hover align-middle mb-0">
                                            <thead class="table-light text-uppercase tracking-wider sticky-top small"
                                                style="font-size: 0.72rem; z-index: 1;">
                                                <tr>
                                                    <th class="text-center" style="width: 12%"></th>
                                                    <th style="width: 44%">
                                                        {{ __('site.invoices.save_complement.internal_folio') }}</th>
                                                    <th style="width: 44%">
                                                        {{ __('site.invoices.save_complement.certification_date') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody style="font-size: 0.85rem;">
                                                @if (!$this->client_is_selected)
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted py-4">
                                                            <i
                                                                class="bi bi-info-circle d-block mb-1 fs-5 text-secondary"></i>
                                                            <span>{{ __('site.invoices.save_complement.select_client_to_view_debts') }}</span>
                                                        </td>
                                                    </tr>
                                                @else
                                                    @if (count($facturasAll) > 0)
                                                        @foreach ($facturasAll as $index => $factura)
                                                            <tr x-data="{
                                                                seleccionada: {{ $factura['seleccionada'] ? 'true' : 'false' }},
                                                                id_factura: '{{ $factura['id'] }}',
                                                                checkFactura(index, id) {
                                                                    this.seleccionada = !this.seleccionada;
                                                                    setTimeout(() => { @this.checkFactura(index, id); }, 100);
                                                                }
                                                            }" x-init="Livewire.on('unselect-factura', id => { if (id_factura == id) { seleccionada = false; } })"
                                                                :class="seleccionada ? 'table-primary-subtle' : ''">
                                                                <td class="text-center">
                                                                    <input type="checkbox" class="form-check-input"
                                                                        x-bind:checked="seleccionada"
                                                                        @click="checkFactura('{{ $index }}', '{{ $factura['id'] }}')">
                                                                </td>
                                                                <td class="fw-semibold font-monospace text-dark">
                                                                    {{ $factura['folio_interno'] }}</td>
                                                                <td class="text-secondary font-monospace small">
                                                                    {{ $factura['fecha_certificacion_str'] }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <tr>
                                                            <td colspan="3" class="text-center text-success py-4">
                                                                <i class="bi bi-check-all d-block mb-1 fs-5"></i>
                                                                <span>{{ __('site.invoices.save_complement.not_pending_invoices') }}</span>
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
                </div>
            </div>

            <div class="col-12">
                <div class="card shadow-sm border rounded-3">
                    <div class="card-header bg-light py-3 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark text-uppercase tracking-wider small"><i
                                class="bi bi-currency-dollar me-2 text-primary"></i>{{ __('site.invoices.save_complement.payment_data') }}
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4 col-sm-6">
                                <label
                                    class="text-muted small fw-bold">{{ __('site.invoices.save_complement.payment_date') }}</label>
                                <input type="date"
                                    class="form-control form-control @error('fecha_pago') is-invalid @enderror"
                                    wire:model="fecha_pago">
                                @error('fecha_pago')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <x-select2-ajax label="{{ __('site.invoices.save_complement.cfdi') }}"
                                    placeholder="{{ __('site.common.select') }}..." class="form-control"
                                    url="{{ route('cfdis.load-cfdis') }}" model="cfdi_id" />
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <label
                                    class="text-muted small fw-bold">{{ __('site.invoices.save_complement.receipt_type') }}</label>
                                <span
                                    class="badge bg-secondary-subtle text-secondary border d-block py-2 fw-bold text-start font-monospace px-3 rounded"
                                    style="font-size: 0.85rem;">P | Pago</span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4 col-12">
                                <x-select2-ajax label="Forma de Pago SAT" placeholder="Seleccione método..."
                                    class="form-control" url="{{ route('formas-pagos.load-formas-pagos') }}"
                                    model="forma_pago_id" />
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <label
                                    class="  text-muted small fw-bold">{{ __('site.invoices.save_complement.currency') }}</label>
                                <select class="form-select form-select-sm @error('moneda') is-invalid @enderror"
                                    wire:model="moneda" id="id_moneda">
                                    @foreach ($moendas as $moneda)
                                        <option value="{{ $moneda }}">{{ $moneda }}</option>
                                    @endforeach
                                </select>
                                @error('moneda')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2 col-sm-6">
                                <label class="text-muted small fw-bold">{{__('site.invoices.save_complement.exchange_rate')}}</label>
                                <input type="number" step="0.0001"
                                    class="form-control form-control font-monospace @error('tipo_cambio') is-invalid @enderror"
                                    disabled wire:model="tipo_cambio">
                                @error('tipo_cambio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 col-12">
                                <label class="text-primary small fw-bold">{{__('site.invoices.save_complement.import_paid')}}</label>
                                <div class="input-group input-group">
                                    <span
                                        class="input-group-text bg-primary-subtle text-primary border-primary-subtle font-monospace fw-bold">$</span>
                                    <input type="text"
                                        class="form-control form-control font-monospace fw-bold bg-light border-start-0 text-dark"
                                        value="{{ number_format((float) $this->total, 2) }}" disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($this->mostrar_datos_cuentas)
                <div class="col-12">
                    <div class="card shadow-sm border border-info rounded-3">
                        <div class="card-header bg-info-subtle py-3 border-bottom border-info-subtle">
                            <h6 class="mb-0 fw-bold text-info-emphasis text-uppercase tracking-wider small"><i
                                    class="bi bi-bank me-2"></i>{{__('site.invoices.save_complement.bank_accounts')}}</h6>
                        </div>
                        <div class="card-body p-4 bg-light bg-opacity-25">
                            <div class="row g-4">
                                <div class="col-xl-3 col-md-4 col-12">
                                    <x-input label="{{__('site.invoices.save_complement.operation_number')}}" class="form-control font-monospace"
                                        model="numero_operacion" />
                                </div>
                                <div class="col-xl-9 col-md-8 col-12">
                                    <div class="row g-2 mb-3 align-items-end">
                                        <div class="col-sm-4"><x-select2 label="{{__('site.invoices.save_complement.origin_account')}}" placeholder="{{__('site.common.select')}}..."
                                                :options="$cuentasOrigen" model="cuenta_origen_id" class="form-control" />
                                        </div>
                                        <div class="col-sm-5">
                                            <label class="text-muted small mb-1">{{__('site.invoices.save_complement.origin_bank')}}</label>
                                            <input type="text" class="form-control form-control bg-light small"
                                                value="{{ $banco_origen_nombre }}" disabled>
                                        </div>
                                        <div class="col-sm-3">
                                            <label class="text-muted small mb-1">{{__('site.invoices.save_complement.origin_bank_rfc')}}</label>
                                            <input type="text"
                                                class="form-control form-control bg-light font-monospace small"
                                                value="{{ $banco_origen_rfc }}" disabled>
                                        </div>
                                    </div>
                                    <div class="row g-2 align-items-end">
                                        <div class="col-sm-4"><x-select2 label="{{__('site.invoices.save_complement.destiny_account')}}"
                                                placeholder="{{__('site.common.select')}}..." :options="$cuentasDestino" model="cuenta_destino_id"
                                                class="form-control" /></div>
                                        <div class="col-sm-5">
                                            <label class="text-muted small mb-1">{{__('site.invoices.save_complement.destiny_bank')}}</label>
                                            <input type="text" class="form-control form-control bg-light small"
                                                value="{{ $banco_destino_nombre }}" disabled>
                                        </div>
                                        <div class="col-sm-3">
                                            <label class="text-muted small mb-1">{{__('site.invoices.save_complement.destiny_bank_rfc')}}</label>
                                            <input type="text"
                                                class="form-control form-control bg-light font-monospace small"
                                                value="{{ $banco_destino_rfc }}" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="col-12">
                <div class="card shadow-sm border rounded-3">
                    <div class="card-header bg-light py-3 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark text-uppercase tracking-wider small"><i
                                class="bi bi-diagram-3 me-2 text-primary"></i>{{__('site.invoices.save_complement.related_invoices')}}
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase tracking-wider small" style="font-size: 0.72rem;">
                                <tr>
                                    <th class="text-center" style="width: 60px;">{{__('site.invoices.save_complement.action')}}</th>
                                    <th class="text-center">{{__('site.invoices.save_complement.folio')}}</th>
                                    <th class="text-center">{{__('site.invoices.save_complement.date')}}</th>
                                    <th class="text-center">{{__('site.invoices.save_complement.currency')}}</th>
                                    <th class="text-center">{{__('site.invoices.save_complement.exchange_rate')}}</th>
                                    <th class="text-end">{{__('site.invoices.save_complement.total')}}</th>
                                    <th class="text-center">{{__('site.invoices.save_complement.partiality_number')}}</th>
                                    <th class="text-end">{{__('site.invoices.save_complement.previous_balance')}}</th>
                                    <th class="text-center" style="width: 180px;">{{__('site.invoices.save_complement.import_paid')}}</th>
                                    <th class="text-end pe-4">{{__('site.invoices.save_complement.balance')}}</th>
                                </tr>
                            </thead>
                            <tbody style="font-size: 0.88rem;">
                                @error('facturas')
                                    <tr>
                                        <td colspan="10" class="p-0">
                                            <div
                                                class="alert alert-danger rounded-1 mb-0 border-0 py-2 font-medium small px-4">
                                                {{ $message }}</div>
                                        </td>
                                    </tr>
                                @enderror

                                @if ($facturas && count($facturas) > 0)
                                    @foreach ($facturas as $index => $factura)
                                        <tr>
                                            <td class="text-center">
                                                <button type="button" wire:click="eliminarFactura({{ $factura['id'] }})"
                                                    class="btn btn-outline-danger btn-sm border-0 rounded-2 shadow-none p-1 d-inline-flex"
                                                    title="Desvincular">
                                                    <i class="bi bi-trash fs-6"></i>
                                                </button>
                                            </td>
                                            <td class="text-center font-monospace fw-semibold text-dark">
                                                {{ $factura['folio_interno'] }}</td>
                                            <td class="text-center font-monospace text-secondary small">
                                                {{ $factura['fecha'] }}</td>
                                            <td class="text-center"><span
                                                    class="badge bg-light text-dark border">{{ $factura['moneda'] }}</span>
                                            </td>
                                            <td class="text-center font-monospace text-secondary">
                                                {{ $factura['tipo_cambio'] }}</td>
                                            <td class="text-end font-monospace fw-medium">
                                                ${{ number_format($factura['total'], 2) }}</td>
                                            <td class="text-center"><span
                                                    class="badge bg-secondary rounded-pill font-monospace px-2.5">{{ $factura['no_parcialidad'] }}</span>
                                            </td>
                                            <td class="text-end font-monospace text-muted">
                                                ${{ number_format($factura['balance_previo_temp'], 2) }}</td>
                                            <td class="text-center">
                                                <div class="px-2">
                                                    <x-input model="facturas.{{ $index }}.importe_pagado"
                                                        wire:change="comprobarSaldoFactura({{ $index }})"
                                                        :debounce="500" type="number" step="0.01"
                                                        class="form-control font-monospace text-center mb-1" />
                                                    <small
                                                        class="text-muted d-block font-monospace"><i>${{ number_format(max($facturas[$index]['importe_pagado'], 0), 2) }}</i></small>
                                                </div>
                                            </td>
                                            <td class="text-end pe-4 font-monospace fw-bold text-primary">
                                                ${{ number_format($this->saldoFactura($index), 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="bi bi-layers-half d-block mb-1 fs-4 text-secondary"></i>
                                            <span>{{__('site.invoices.save_complement.not_related_invoices')}}</span>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="text-muted small fw-bold">{{__('site.invoices.save_complement.quantity_in_words')}}</label>
                        <input type="text" class="form-control form-control bg-light fw-medium text-dark px-3 py-2"
                            wire:model="cantidad_letras" disabled>
                    </div>
                    <div class="col-12 mb-4">
                        <x-textarea label="{{__('site.invoices.save_complement.comments')}}"
                            model="comentarios" placeholder="{{__('site.invoices.save_complement.comments_placeholder')}}..."
                            rows="3" />
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade {{ $iframeContainerClass ? 'show d-block bg-dark bg-opacity-50' : '' }}" tabindex="-1"
            role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="modal-header bg-light py-3 px-4 border-bottom">
                        <h5 class="modal-title fw-bold text-dark"><i
                                class="bi bi-file-earmark-pdf text-danger me-2"></i>PDF
                        </h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"
                            wire:click="$set('iframeContainerClass', '')"></button>
                    </div>
                    <div class="modal-body p-0 bg-secondary">
                        <iframe src="{{ $iframeSrc }}" frameborder="0" id="frame-death-file" class="w-100"
                            style="height: 75vh;"></iframe>
                    </div>
                    <div class="modal-footer bg-light py-2.5 px-4 border-top">
                        <button type="button" class="btn btn-secondary fw-semibold px-4" data-bs-dismiss="modal"
                            wire:click="$set('iframeContainerClass', '')">{{__('site.common.close')}}</button>
                    </div>
                </div>
            </div>
        </div>

        @livewire('facturas-sistema.consecutivo-factura')
    </div>
