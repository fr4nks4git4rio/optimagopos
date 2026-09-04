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

        .nav-item>.nav-link.active {
            color: #d25527;
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
                            {{__('site.self_billing_stamp.emitter_details')}}
                        </div>
                        <div class="card-body">
                            <div class="col-12">
                                <div class="mb-1">
                                    <label for="">{{__('site.self_billing_stamp.rfc')}}:</label>
                                    <input type="text" class="form-control" value="{{ $this->propietario_rfc }}"
                                        disabled>
                                </div>
                                <div class="mb-1">
                                    <label for="">{{__('site.self_billing_stamp.name')}}:</label>
                                    <input type="text" class="form-control"
                                        value="{{ $this->propietario_razon_social }}" disabled>
                                </div>
                                <div class="mb-1">
                                    <label for="">{{__('site.self_billing_stamp.issued_in')}}:</label>
                                    <input type="text" class="form-control" value="{{ $factura->lugar_expedicion }}"
                                        disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 mb-2">
                    <div class="card h-100">
                        <div class="card-header">
                            {{__('site.self_billing_stamp.receiver_details')}}
                        </div>
                        <div class="card-body">
                            <div class="col-12">
                                <x-input label="{{__('site.self_billing_stamp.rfc')}}" model="rfc" :lazy="true" />
                                <x-input label="{{__('site.self_billing_stamp.trade_name')}}" model="nombre_comercial" />
                                <x-input label="{{__('site.self_billing_stamp.social_reason')}}" model="razon_social" />
                                <x-input label="{{__('site.self_billing_stamp.postal_code')}}" model="lugar_expedicion" />
                                <x-select2 label="{{__('site.self_billing_stamp.fiscal_regime')}}" class="form-control" :options="$regimenesFiscales"
                                    model="regimen_fiscal_id" />
                                <x-select2 label="{{__('site.self_billing_stamp.cfdi')}}" class="form-control" :lazy="true" :options="$cfdis"
                                    model="cfdi_id" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 mb-2">
                    <div class="row">
                        <div class="col-sm-12 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    {{__('site.self_billing_stamp.billing_details')}}
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-6 col-xs-12">
                                            <div class="mb-1">
                                                <label for="">{{__('site.self_billing_stamp.payment_form')}}:</label>
                                                <input type="text" class="form-control"
                                                    value="{{ $factura->forma_pago->label }}" disabled>
                                            </div>
                                        </div>
                                        <div class="col-sm-3 col-xs-12">
                                            <div class="mb-1">
                                                <label for="">{{__('site.self_billing_stamp.import')}}:</label>
                                                <input type="text" class="form-control"
                                                    value="${{ number_format($this->totalFacturar(), 2) }}" disabled>
                                            </div>
                                        </div>
                                        <div class="col-sm-3 col-xs-12">
                                            <div class="mb-1">
                                                <label for="">{{__('site.self_billing_stamp.currency')}}:</label>
                                                <input type="text" class="form-control"
                                                    value="{{ $factura->moneda }}" disabled>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    {{__('site.self_billing_stamp.invoice_concepts')}}
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i>{{__('site.self_billing_stamp.info_1')}}</i>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-md-2">
                                            <x-toggle-button label="{{__('site.self_billing_stamp.tip_included')}}" :inline="true" :lazy="true"
                                                class="float-end" model="incluir_propina" />
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <x-toggle-button label="{{__('site.self_billing_stamp.group_by_concept')}}" :inline="true"
                                                :lazy="true" class="float-end" model="agrupar_conceptos" />
                                        </div>
                                        <div class="col-12 col-md-4 mb-2">
                                            <select class="form-control" wire:model.live="concepto_agrupado"
                                                @if (!$agrupar_conceptos) disabled @endif>
                                                @foreach ($posiblesConceptos as $concepto)
                                                    <option value="{{ $concepto }}">
                                                        {{ $concepto }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4 mb-2 text-center">
                                            @if ($factura_timbrada)
                                                <button type="button" class="btn btn-success mb-3"
                                                    wire:click="descargarPDF">{{__('site.common.download_pdf')}}</button>
                                                <button type="button" class="btn btn-info mb-3"
                                                    wire:click="descargarXML">{{__('site.common.download_xml')}}</button>
                                            @else
                                                <button type="button" class="btn btn-secondary mb-3"
                                                    wire:click="showModalCfdisRelacionados">
                                                    {{__('site.self_billing_stamp.add_related_cfdi')}}</button>
                                                <button type="button" class="btn btn-primary mb-3"
                                                    wire:loading.attr="disabled" wire:click="timbrar()">
                                                    <div wire:loading.remove>
                                                        {{__('site.common.stamp')}}
                                                    </div>
                                                    <div wire:loading>
                                                        <i
                                                            class="material-icons spinner-border spinner-border-sm"></i>&nbsp;{{__('site.common.loading')}}...
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
            </div>
        </div>
        <div class="modal fade" id="modal-cfdis" tabindex="-1" wire:ignore.self>
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('site.self_billing_stamp.related_cfdis') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body pb-0">
                        <div class="row mb-2">
                            <div class="col-12 col-md-2">
                                <label for="">{{ __('site.self_billing_stamp.relation_type') }}</label>
                            </div>
                            <div class="col-12 col-md-7 mb-2">
                                <x-select2-modals class="form-control" :options="$tiposRelacionFactura" :dynamic="true"
                                    model="tipo_relacion_factura_id" />
                            </div>
                            <div class="col-12 col-md-3 mb-2">
                                <button type="button" class="btn btn-primary"
                                    wire:click="addCfdiRelacionado">
                                    {{ __('site.self_billing_stamp.add_uuid') }}
                                </button>
                            </div>
                        </div>
                        <div class="row mb-2">
                            @foreach ($cfdis_relacionados as $index => $cfdi)
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control"
                                        placeholder="________-____-____-____-____________"
                                        aria-label="CFDI relacionado"
                                        aria-describedby="button-addon-cfdi{{ $index }}"
                                        wire:model.blur="cfdis_relacionados.{{ $index }}">
                                    <button class="btn btn-danger" type="button"
                                        id="button-addon-cfdi{{ $index }}"
                                        wire:click="removeCfdiRelacionado('{{ $index }}')"><i
                                            class="bi bi-trash"></i></button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cerrar') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- AdminLTE retirado: las vistas usan modales Bootstrap 5 nativos (sin dependencias AdminLTE) --}}
