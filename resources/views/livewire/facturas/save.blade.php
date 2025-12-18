@section('title', ($factura->exists ? 'Editar ' : 'Nueva ') . 'Factura')

<div>
    <div wire:loading.delay>
        <div class="loading">
            <img src="{{ asset('img/loading.gif') }}" />
        </div>
    </div>

    <h1 class="fs-1 mb-3">@yield('title')</h1>

    @error('user_id')
        <div class="alert alert-danger text-center">
            {{ $message }}
        </div>
    @enderror

    <fieldset>
        <legend class="border-bottom">Resumen de Tickets</legend>
        <div class="row mb-3">
            <div class="col-sm-7 col-xs-12">
                <div class="row">
                    <div class="col-sm-12">
                        <x-select2 label="Sucursal" placeholder="Seleccione..." :options="$sucursales" class="form-control"
                            model="propietario_id" />
                    </div>
                    <div class="col-sm-3">
                        <x-input label="Fecha inicio" type="date" model="fecha_inicio" />
                    </div>
                    <div class="col-sm-3">
                        <x-input label="Fecha fin" type="date" model="fecha_fin" />
                    </div>
                    <div class="col-sm-4">
                        <x-select2 label="Forma de Pago" placeholder="Seleccione..." :options="$formasPagoSucursal"
                            class="form-control" model="forma_pago" :dynamic="true" />
                    </div>
                    <div class="col-sm-2" style="padding-top: 23px">
                        <button type="button" class="btn btn-site-primary" wire:click="loadImporteFacturar">
                            <x-icon name="search" /> Buscar
                        </button>
                    </div>
                    <div class="col-sm-12 mt-3">
                        @if ($validacion_operaciones_pendientes)
                            <div class="alert alert-danger">
                                {!! $validacion_operaciones_pendientes !!}
                            </div>
                        @else
                            <div class="alert alert-info">
                                Seleccione la sucursal, el intervalo de de fechas a facturar y la forma de pago para
                                comenzar.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-sm-5 table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Forma Pago</th>
                            <th class="text-center">Subtotal</th>
                            <th class="text-center">IVA</th>
                            <th class="text-center">Total</th>
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
                                        No se han encontrado resultados...
                                    </div>
                                    @error('tickets')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                        @endforelse
                        @if (count($tickets) > 0)
                            <tr>
                                <td class="text-end" colspan="2"><strong>TOTAL</strong></td>
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
        <legend class="border-bottom">Encabezado de Factura</legend>
        <div class="row">
            <div class="col-sm-5 col-xs-12">
                <div class="mb-1">
                    <label for="">Receptor:</label>
                    <input type="text" class="form-control" value="{{ $this->nombre_receptor }}" disabled>
                    @error('cliente_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-sm-3 col-xs-12">
                <div class="mb-1">
                    <label for="">RFC:</label>
                    <input type="text" class="form-control" value="{{ $this->rfc_receptor }}" disabled>
                </div>
            </div>
            <div class="col-sm-2 col-xs-12">
                <div class="mb-1">
                    <label for="">Lugar Expedición:</label>
                    <input type="text" class="form-control" value="{{ $lugar_expedicion }}" disabled>
                </div>
            </div>
            <div class="col-sm-2 col-xs-12">
                <div class="mb-1">
                    <label for="">Fecha:</label>
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
                    <label for="">Dirección Fiscal:</label>
                    <input type="text" class="form-control" value="{{ $this->direccion_fiscal_receptor }}" disabled>
                </div>
            </div>
            <div class="col-sm-4 col-xs-12">
                <div class="mb-1">
                    <label for="">Régimen Fiscal:</label>
                    <input type="text" class="form-control" value="{{ $this->regimen_fiscal_receptor }}" disabled>
                    @error('regimen_receptor')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-sm-2 col-xs-12">
                <x-select2 label="Serie" placeholder="Seleccione..." :options="$series" class="form-control"
                    model="serie_id" />
            </div>
            <div class="col-sm-2 col-xs-12">
                <div class="mb-1">
                    <label for="">Moneda:</label>
                    <input type="text" class="form-control" value="{{ $this->moneda }}" disabled>
                    @error('moneda')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3 col-xs-12">
                <x-select2 label="Método de Pago" placeholder="Seleccione..." :options="$metodosPago" class="form-control"
                    model="metodo_pago_id" />
            </div>
            <div class="col-sm-3 col-xs-12">
                <x-select2 label="Forma de Pago" placeholder="Seleccione..." :options="$formasPago" class="form-control"
                    model="forma_pago_id" :dynamic="true" disabled />
            </div>
            <div class="col-sm-3 col-xs-12">
                <x-select2 label="Uso CFDI" placeholder="Seleccione..." :options="$usosCfdi" class="form-control"
                    model="cfdi_id" />
            </div>
            <div class="col-sm-3 col-xs-12">
                @if ($this->moneda == 'USD')
                    <div class="mb-1">
                        <label for="">Tipo de Cambio:</label>
                        <input type="text" class="form-control" value="{{ $this->tipo_cambio }}" disabled>
                        @error('tipo_cambio')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-sm-4 col-xs-12">
                <x-select2 label="Periodicidad" placeholder="Seleccione..." :options="$periodicidades" class="form-control"
                    model="periodicidad_id" />
            </div>
            <div class="col-sm-4 col-xs-12">
                <x-select2 label="Mes" placeholder="Seleccione..." :options="$meses" class="form-control"
                    model="mes_id" />
            </div>
            <div class="col-sm-4 col-xs-12">
                <div class="mb-1">
                    <label for="">Año:</label>
                    <select class="form-control" wire:model="anio">
                        <option value="">Seleccione...</option>
                        @foreach ($anios as $anio)
                            <option value="{{ $anio }}">{{ $anio }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend class="border-bottom">Agregar Concepto</legend>
        <div class="table-responsive">
            <table class="table table-striped">
                <tbody>
                    <tr>
                        <td class="text-center">{{ $concepto['cantidad'] }}</td>
                        <td>
                            <x-select2 placeholder="Clave Prod. Serv." :options="$clavesProdServ" class="form-control"
                                model="concepto.clave_prod_serv_id" />
                        </td>
                        <td>
                            <x-select2 placeholder="Clave Unidad" :options="$clavesUnidad" class="form-control"
                                model="concepto.clave_unidad_id" />
                        </td>
                        <td>
                            <x-select2 placeholder="Objeto Impuesto" :options="$objetosImpuesto" class="form-control"
                                model="concepto.objeto_impuesto_id" />
                        </td>
                        <td>
                            <textarea class="form-control" rows="1" wire:model="concepto.descripcion"></textarea>
                        </td>
                        <td>
                            <x-input model="concepto.precio_unitario" type="number" placeholder="Precio Unitario" />
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-site-primary" wire:click="addConcepto">
                                Aceptar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </fieldset>
    <fieldset>
        <legend class="border-bottom">Concepto de Facturación</legend>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-center">Cantidad</th>
                        <th class="text-center">Clave Prod. Serv.</th>
                        <th class="text-center">Clave Unidad</th>
                        <th class="text-center">Objeto Impuesto</th>
                        <th class="text-center">Concepto</th>
                        <th class="text-center">Valor Unitario</th>
                        <th class="text-center">Importe</th>
                        <th class="text-center">Acciones</th>
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
                                        <x-action icon="trash" title="Eliminar"
                                            click="mostrarModalEliminarConcepto('{{ $index }}')" />
                                    </li>
                                </ul>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="alert alert-info mb-0 text-center">
                                    Sin conceptos...
                                </div>
                                @error('factura_conceptos')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>
                    @endforelse
                    @if (count($factura_conceptos) > 0)
                        <tr>
                            <td class="text-end" colspan="6"><strong>Subtotal:</strong></td>
                            <td class="text-center">
                                <strong>${{ number_format($this->subtotal_factura, 2) }}</strong>
                                @error('subtotal')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-end" colspan="6"><strong>IVA:</strong></td>
                            <td class="text-center">
                                <strong>${{ number_format($this->iva_factura, 2) }}</strong>
                                @error('iva')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-end" colspan="6"><strong>Total:</strong></td>
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
            <label for="">Importe con letras:</label>
            <input type="text" class="form-control" value="{{ $this->importe_letras_factura }}" disabled>
        </div>
    </div>
    <div class="col-sm-12">
        <div class="mb-1">
            <label for="">Observaciones:</label>
            <textarea class="form-control" rows="4" wire:model="comentarios"></textarea>
        </div>
    </div>
    <div class="col-sm-12 pt-3 text-end">
        <button type="button" class="btn btn-secondary" wire:click="goToList">
            Cancelar
        </button>
        <button type="button" wire:loading.attr="disabled" class="btn btn-site-primary" wire:click="guardar">
            Guardar
        </button>
    </div>

    <div class="modal {{ $modalDeleteConceptoClass }}">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        wire:click="$set('modalDeleteConceptoClass', '')"></button>
                </div>
                <div class="modal-body pb-0 text-center">
                    <x-alert icon="exclamation-octagon" alert="danger">
                        Seguro/a que desea eliminar el concepto?
                    </x-alert>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        wire:click="$set('modalDeleteConceptoClass', '')">{{ __('Cerrar') }}</button>
                    <button type="button" class="btn btn-site-primary" wire:click="eliminarConcepto()">Eliminar
                        Concepto</button>
                </div>
            </div>
        </div>
    </div>
</div>
