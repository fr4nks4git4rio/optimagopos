@section('title',
    ($factura->exists ? __('site.invoices.save_invoice.edit') : __('site.invoices.save_invoice.new')) .
    '
    ' .
    __('site.common.invoice'))

    <div wire:init="init">
        <div wire:loading.delay>
            <div class="loading">
                <div class="spinner-border text-primary my-3" role="status"><span class="visually-hidden">Cargando...</span></div>
            </div>
        </div>

        <h1 class="fs-1 mb-3">@yield('title')</h1>

        @error('user_id')
            <div class="alert alert-danger text-center">
                {{ $message }}
            </div>
        @enderror

        <fieldset>
            <legend class="border-bottom">{{ __('site.invoices.save_invoice.ticket_summary') }}</legend>
            <div class="row mb-3">
                <div class="col-sm-7 col-xs-12">
                    <div class="row">
                        <div class="col-sm-12">
                            <x-select2 label="{{ __('site.invoices.save_invoice.branch') }}"
                                placeholder="{{ __('site.common.select') }}..." :options="$sucursales" class="form-control"
                                model="propietario_id" />
                        </div>
                        <div class="col-sm-3">
                            <x-input label="{{ __('site.invoices.save_invoice.start_date') }}" type="date"
                                model="fecha_inicio" />
                        </div>
                        <div class="col-sm-3">
                            <x-input label="{{ __('site.invoices.save_invoice.end_date') }}" type="date"
                                model="fecha_fin" />
                        </div>
                        <div class="col-sm-4">
                            <x-select2 label="{{ __('site.invoices.save_invoice.payment_form') }}"
                                placeholder="{{ __('site.common.select') }}..." :options="$formasPagoSucursal" class="form-control"
                                model="forma_pago" :lazy="false" :dynamic="true" />
                        </div>
                        <div class="col-sm-2" style="padding-top: 23px">
                            <button type="button" class="btn btn-site-primary" wire:click="loadImporteFacturar">
                                <x-icon name="search" /> {{ __('site.invoices.save_invoice.search') }}
                            </button>
                        </div>
                        <div class="col-sm-12 mt-3">
                            @if ($validacion_operaciones_pendientes)
                                <div class="alert alert-danger">
                                    {!! $validacion_operaciones_pendientes !!}
                                </div>
                            @else
                                <div class="alert alert-info">
                                    {{ __('site.invoices.save_invoice.select_branch_and_period') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-sm-5 table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th class="text-center">{{ __('site.invoices.save_invoice.date') }}</th>
                                <th class="text-center">{{ __('site.invoices.save_invoice.payment_form') }}</th>
                                <th class="text-center">{{ __('site.invoices.save_invoice.subtotal') }}</th>
                                <th class="text-center">{{ __('site.invoices.save_invoice.iva') }}</th>
                                <th class="text-center">{{ __('site.invoices.save_invoice.total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tickets as $key => $ticket)
                                <tr>
                                    <td class="text-center">{{ $ticket['fecha'] }}</td>
                                    <td class="text-center">{{ $ticket['forma_pago'] }}</td>
                                    <td class="text-center">${{ number_format($ticket['subtotal'], 2) }}</td>
                                    <td class="text-center">${{ number_format($ticket['iva'], 2) }}</td>
                                    <td class="text-center">${{ number_format($ticket['total'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="alert alert-info mb-0 text-center">
                                            {{ __('site.common.results_not_found') }}...
                                        </div>
                                        @error('tickets')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </td>
                                </tr>
                            @endforelse
                            @if (count($tickets) > 0)
                                <tr>
                                    <td class="text-end" colspan="2">
                                        <strong>{{ __('site.invoices.save_invoice.total') }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <strong>${{ number_format($this->subtotal_facturar, 2) }}</strong>
                                    </td>
                                    <td class="text-center"><strong>${{ number_format($this->iva_facturar, 2) }}</strong>
                                    </td>
                                    <td class="text-center"><strong>${{ number_format($this->total_facturar, 2) }}</strong>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend class="border-bottom">{{ __('site.invoices.save_invoice.invoice_header') }}</legend>
            <div class="row">
                <div class="col-sm-5 col-xs-12">
                    <div class="mb-1">
                        <label for="">{{ __('site.invoices.save_invoice.receiver') }}:</label>
                        <input type="text" class="form-control" value="{{ $this->nombre_receptor }}" disabled>
                        @error('cliente_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-sm-3 col-xs-12">
                    <div class="mb-1">
                        <label for="">{{ __('site.invoices.save_invoice.rfc') }}:</label>
                        <input type="text" class="form-control" value="{{ $this->rfc_receptor }}" disabled>
                    </div>
                </div>
                <div class="col-sm-2 col-xs-12">
                    <div class="mb-1">
                        <label for="">{{ __('site.invoices.save_invoice.postal_code') }}:</label>
                        <input type="text" class="form-control" value="{{ $lugar_expedicion }}" disabled>
                    </div>
                </div>
                <div class="col-sm-2 col-xs-12">
                    <div class="mb-1">
                        <label for="">{{ __('site.invoices.save_invoice.date') }}:</label>
                        <input type="text" class="form-control" value="{{ $this->fecha_emision_str }}" disabled>
                        @error('fecha_emision')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4 col-xs-12">
                    <div class="mb-1">
                        <label for="">{{ __('site.invoices.save_invoice.fiscal_address') }}:</label>
                        <input type="text" class="form-control" value="{{ $this->direccion_fiscal_receptor }}" disabled>
                    </div>
                </div>
                <div class="col-sm-4 col-xs-12">
                    <div class="mb-1">
                        <label for="">{{ __('site.invoices.save_invoice.fiscal_regime') }}:</label>
                        <input type="text" class="form-control" value="{{ $this->regimen_fiscal_receptor }}" disabled>
                        @error('regimen_receptor')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-sm-2 col-xs-12">
                    <div class="mb-1">
                        <label for="">{{ __('site.invoices.save_invoice.currency') }}:</label>
                        <input type="text" class="form-control" value="{{ $this->moneda }}" disabled>
                        @error('moneda')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-sm-2 col-xs-12">
                    <div class="mb-1">
                        <label for="">{{ __('site.invoices.save_invoice.exchange_rate') }}:</label>
                        <input type="text" class="form-control" value="{{ $this->tipo_cambio }}" disabled>
                        @error('tipo_cambio')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-2 col-xs-12">
                    <x-select2 label="{{ __('site.invoices.save_invoice.serie') }}"
                        placeholder="{{ __('site.common.select') }}..." :options="$series" class="form-control"
                        model="serie_id" />
                </div>
                <div class="col-sm-3 col-xs-12">
                    <x-select2 label="{{ __('site.invoices.save_invoice.payment_method') }}"
                        placeholder="{{ __('site.common.select') }}..." :options="$metodosPago" class="form-control"
                        model="metodo_pago_id" />
                </div>
                <div class="col-sm-3 col-xs-12">
                    <x-select2 label="{{ __('site.invoices.save_invoice.payment_form') }}"
                        placeholder="{{ __('site.common.select') }}..." :options="$formasPago" class="form-control"
                        model="forma_pago_id" :dynamic="true" disabled />
                </div>
                <div class="col-sm-4 col-xs-12">
                    <x-select2 label="{{ __('site.invoices.save_invoice.cfdi') }}"
                        placeholder="{{ __('site.common.select') }}..." :options="$usosCfdi" class="form-control"
                        model="cfdi_id" />
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4 col-xs-12">
                    <x-select2 label="{{ __('site.invoices.save_invoice.periodicity') }}"
                        placeholder="{{ __('site.common.select') }}..." :options="$periodicidades" class="form-control"
                        model="periodicidad_id" />
                </div>
                <div class="col-sm-4 col-xs-12">
                    <x-select2 label="{{ __('site.invoices.save_invoice.period_month') }}"
                        placeholder="{{ __('site.common.select') }}..." :options="$meses" class="form-control"
                        model="mes_id" />
                </div>
                <div class="col-sm-4 col-xs-12">
                    <div class="mb-1">
                        <label for="">{{ __('site.invoices.save_invoice.year') }}:</label>
                        <select class="form-control" wire:model.live="anio">
                            <option value="">{{ __('site.common.select') }}...</option>
                            @foreach ($anios as $anio)
                                <option value="{{ $anio }}">{{ $anio }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend class="border-bottom">{{ __('site.invoices.save_invoice.add_concept') }}</legend>
            <div class="table-responsive">
                <table class="table table-striped">
                    <tbody>
                        <tr>
                            <td class="text-center">{{ $concepto['cantidad'] }}</td>
                            <td>
                                <x-select2 placeholder="{{ __('site.invoices.save_invoice.prod_serv_code') }}"
                                    :options="$clavesProdServ" class="form-control" model="concepto.clave_prod_serv_id" />
                            </td>
                            <td>
                                <x-select2 placeholder="{{ __('site.invoices.save_invoice.unit_code') }}Clave Unidad"
                                    :options="$clavesUnidad" class="form-control" model="concepto.clave_unidad_id" />
                            </td>
                            <td>
                                <x-select2 placeholder="{{ __('site.invoices.save_invoice.tax_base') }}"
                                    :options="$objetosImpuesto" class="form-control" model="concepto.objeto_impuesto_id" />
                            </td>
                            <td>
                                <textarea class="form-control" rows="1" wire:model="concepto.descripcion"
                                    placeholder="{{ __('site.invoices.save_invoice.concept') }}"></textarea>
                            </td>
                            <td>
                                <x-input model="concepto.precio_unitario" type="number"
                                    placeholder="{{ __('site.invoices.save_invoice.unit_value') }}" />
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-site-primary" wire:click="addConcepto">
                                    {{ __('site.common.accept') }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </fieldset>
        <fieldset>
            <legend class="border-bottom">{{ __('site.invoices.save_invoice.billing_concept') }}</legend>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="text-center">{{ __('site.invoices.save_invoice.quantity') }}</th>
                            <th class="text-center">{{ __('site.invoices.save_invoice.prod_serv_code') }}</th>
                            <th class="text-center">{{ __('site.invoices.save_invoice.unit_code') }}</th>
                            <th class="text-center">{{ __('site.invoices.save_invoice.tax_base') }}</th>
                            <th class="text-center">{{ __('site.invoices.save_invoice.concept') }}</th>
                            <th class="text-center">{{ __('site.invoices.save_invoice.unit_value') }}</th>
                            <th class="text-center">{{ __('site.invoices.save_invoice.import') }}</th>
                            <th class="text-center">{{ __('site.common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($factura_conceptos as $index => $concepto)
                            <tr>
                                <td class="text-center">{{ $concepto['cantidad'] }}</td>
                                <td class="text-center">{{ $concepto['clave_prod_serv'] }}</td>
                                <td class="text-center">{{ $concepto['clave_unidad'] }}</td>
                                <td class="text-center">{{ $concepto['objeto_impuesto'] }}</td>
                                <td class="text-center">{{ $concepto['descripcion'] }}</td>
                                <td class="text-center">${{ number_format($concepto['precio_unitario'], 2) }}</td>
                                <td class="text-center">${{ number_format($concepto['precio_unitario'], 2) }}</td>
                                <td class="text-center">
                                    <ul class="list-unstyled mb-0">
                                        <li class="list-inline-item mb-1">
                                            <x-action icon="trash" title="{{ __('site.common.delete') }}"
                                                click="mostrarModalEliminarConcepto('{{ $index }}')" />
                                        </li>
                                    </ul>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="alert alert-info mb-0 text-center">
                                        {{ __('site.common.results_not_found') }}...
                                    </div>
                                    @error('factura_conceptos')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                        @endforelse
                        @if (count($factura_conceptos) > 0)
                            <tr>
                                <td class="text-end" colspan="6">
                                    <strong>{{ __('site.invoices.save_invoice.subtotal') }}:</strong>
                                </td>
                                <td class="text-center">
                                    <strong>${{ number_format($this->subtotal_factura, 2) }}</strong>
                                    @error('subtotal')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class="text-end" colspan="6">
                                    <strong>{{ __('site.invoices.save_invoice.iva') }}:</strong>
                                </td>
                                <td class="text-center">
                                    <strong>${{ number_format($this->iva_factura, 2) }}</strong>
                                    @error('iva')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class="text-end" colspan="6">
                                    <strong>{{ __('site.invoices.save_invoice.total') }}:</strong>
                                </td>
                                <td class="text-center">
                                    <strong>${{ number_format($this->total_factura, 2) }}</strong>
                                    @error('total')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </td>
                                <td></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </fieldset>
        <div class="col-sm-12">
            <div class="mb-1">
                <label for="">{{ __('site.invoices.save_invoice.quantity_in_words') }}:</label>
                <input type="text" class="form-control" value="{{ $this->importe_letras_factura }}" disabled>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="mb-1">
                <label for="">{{ __('site.invoices.save_invoice.comments') }}:</label>
                <textarea class="form-control" rows="4" wire:model.live="comentarios"></textarea>
            </div>
        </div>
        <div class="col-sm-12 pt-3 text-end">
            <button type="button" class="btn btn-secondary" wire:click="goToList">
                {{ __('site.common.cancel') }}
            </button>
            <button type="button" wire:loading.attr="disabled" class="btn btn-site-primary" wire:click="guardar">
                {{ __('site.common.save') }}
            </button>
        </div>

        <div class="modal fade" id="modal-confirm-delete-concept" tabindex="-1" wire:ignore.self>
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('site.common.confirm') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body pb-0 text-center">
                        <x-alert icon="exclamation-octagon" alert="danger">
                            {{ __('site.invoices.save_invoice.delete_concepto_confirmation') }}
                        </x-alert>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Cerrar') }}</button>
                        <button type="button" class="btn btn-site-primary" wire:click="eliminarConcepto()">
                            {{ __('site.common.delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
