@section('title', 'Inicio')
@push('styles')
<style>
    body {
        height: 100vh;
        background: #e3e3e3;
    }

    .div-facturador {
        border-radius: 10px;
        box-shadow: 0px 0px 10px 0px gray;
        min-height: 100px;
        background: #fff;
    }
</style>
@endpush

<div>
    <div class="row py-2 px-2 justify-content-center">
        <div class="col-12 col-md-10">
            <div class="row">
                <div class="col-12 col-md-6 mb-2">
                    <div class="card h-100">
                        <div class="card-header">
                            Datos de Emisor
                        </div>
                        <div class="card-body">
                            <div class="col-12">
                                <div class="mb-1">
                                    <label for="">RFC:</label>
                                    <input type="text" class="form-control" value="{{ $this->propietario_rfc }}" disabled>
                                </div>
                                <div class="mb-1">
                                    <label for="">Nombre:</label>
                                    <input type="text" class="form-control" value="{{ $this->propietario_razon_social }}" disabled>
                                </div>
                                <div class="mb-1">
                                    <label for="">Expedido en:</label>
                                    <input type="text" class="form-control" value="{{ $factura->lugar_expedicion }}" disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <div class="card h-100">
                        <div class="card-header">
                            Datos del Receptor
                        </div>
                        <div class="card-body">
                            <div class="col-12">
                                <div class="mb-1">
                                    <label for="">RFC:</label>
                                    <input type="text" class="form-control" value="{{ $this->cliente_rfc }}" disabled>
                                </div>
                                <div class="mb-1">
                                    <label for="">Nombre:</label>
                                    <input type="text" class="form-control" value="{{ $this->cliente_razon_social }}" disabled>
                                </div>
                                <div class="mb-1">
                                    <label for="">Expedido en:</label>
                                    <input type="text" class="form-control" value="{{ $this->cliente_codigo_postal }}" disabled>
                                </div>
                                <x-select2 label="Régimen Fiscal" class="form-control" disabled :options="$regimenesFiscales" model="regimen_fiscal_id" />
                                <x-select2 label="Uso CFDI" class="form-control" :lazy="true" :options="$cfdis" model="cfdi_id" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 mb-2">
                    <div class="card">
                        <div class="card-header">
                            Conceptos de Facturación
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i>Si la opción "Agrupar por concepto" se encuentra desmarcada se desglosarán los conceptos en la factura.</i>
                            </div>
                            <div class="row">
                                <div class="col-12 col-md-2">
                                    <x-toggle-button label="Incluir Propina" :inline="true" :lazy="true"
                                        class="float-end" model="incluir_propina" />
                                </div>
                                <div class="col-12 col-md-2">
                                    <x-toggle-button label="Agrupar por Concepto" :inline="true" :lazy="true"
                                        class="float-end" model="agrupar_conceptos" />
                                </div>
                                <div class="col-12 col-md-4 mb-2">
                                    <select class="form-control" wire:model="concepto_agrupado"
                                        @if(!$agrupar_conceptos) disabled @endif>
                                        @foreach($posiblesConceptos as $concepto)
                                        <option value="{{ $concepto }}">{{ $concepto }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-4 mb-2 text-center">
                                    @if($this->factura_timbrada)
                                    <button type="button" class="btn btn-success mb-3"
                                        wire:click="descargarPDF">Descargar PDF</button>
                                    <button type="button" class="btn btn-info mb-3"
                                        wire:click="descargarXML">Descargar XML</button>
                                    @else
                                    <button type="button" class="btn btn-secondary mb-3"
                                        wire:click="$set('cfdisModalClass', 'show')">Agregar CFDI relacionados</button>
                                    <button type="button" class="btn btn-primary mb-3" wire:loading.attr="disabled"
                                        wire:click="timbrar">
                                        <div wire:loading.remove>
                                            Timbrar
                                        </div>
                                        <div wire:loading>
                                            <i class="material-icons spinner-border spinner-border-sm"></i>&nbsp;Procesando...
                                        </div>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal {{$cfdisModalClass}}" id="modal-cfdis">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">CFDIs relacionados</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            wire:click="$set('cfdisModalClass', '')"></button>
                    </div>
                    <div class="modal-body pb-0">
                        <div class="row mb-2">
                            <div class="col-12 col-md-2">
                                <label for="">Tipo de relación</label>
                            </div>
                            <div class="col-12 col-md-7 mb-2">
                                <x-select2-modals class="form-control" :options="$tiposRelacionFactura" model="tipo_relacion_factura_id" />
                            </div>
                            <div class="col-12 col-md-3 mb-2">
                                <button type="button" class="btn btn-primary"
                                    wire:click="addCfdiRelacionado">Agregar UUID</button>
                            </div>
                        </div>
                        <div class="row mb-2">
                            @foreach($cfdis_relacionados as $index => $cfdi)
                            <div class="input-group mb-3">
                                <input type="text" class="form-control"
                                    placeholder="________-____-____-____-____________" aria-label="CFDI relacionado"
                                    aria-describedby="button-addon-cfdi{{ $index }}"
                                    wire:model.lazy="cfdis_relacionados.{{ $index }}">
                                <button class="btn btn-danger" type="button" id="button-addon-cfdi{{ $index }}" wire:click="removeCfdiRelacionado('{{ $index }}')"><i class="bi bi-trash"></i></button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            wire:click="$set('cfdisModalClass', '')">{{ __('Cerrar') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
@endpush
