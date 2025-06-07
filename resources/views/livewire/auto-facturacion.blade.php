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
    <div class="row justify-content-center">
        <div class="col-sm-4 div-facturador py-3">
            @if($this->logo)
            <div class="text-center mb-3">
                <img src="{{ $this->logo }}" class="m-auto" alt="" style="max-height: 200px; border-radius: 5px;">
            </div>
            @endif
            <div class="alert alert-success mb-3">
                Bienvenido(a) a nuestro portal de facturación.
                Recuerde que la información para generar su factura se encuentra en su ticket de compra.
                Contáctenos si presenta algún inconveniente.
            </div>
            <div class="mb-3 pb-2 border-bottom">
                <x-select2 label="Sucursal" placeholder="Seleccione..."
                    :options="$sucursales" :lazy="true"
                    class="form-control"
                    model="suc" />
                @if($this->sucursal)
                <div class="mb-1">
                    <label for="">Teléfono:</label>
                    <input type="text" class="form-control" value="{{ $this->telefono }}" disabled>
                </div>
                <div class="mb-1">
                    <label for="">Correo:</label>
                    <input type="text" class="form-control" value="{{ $this->correo }}" disabled>
                </div>
                @endif
            </div>
            <div class="mb-3 pb-2 border-bottom">
                <div class="mb-1">
                    <label for="">Código:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" wire:model="codigo">
                        <span class="input-group-text" style="cursor: pointer;" wire:click="mostrarTicketMuestra"><x-icon name="exclamation-circle" /></span>
                        @error('codigo')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="mb-1">
                    <label for="">No. Ticket:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" wire:model="ticket">
                        <span class="input-group-text" style="cursor: pointer;" wire:click="mostrarTicketMuestra"><x-icon name="exclamation-circle" /></span>
                        @error('ticket')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="mb-1">
                    <label for="">RFC:</label>
                    <input type="text" class="form-control" wire:model.lazy="rfc">
                    @error('rfc')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                @if($rfc_exists)
                <div class="mb-1">
                    <label for="">Razón Social:</label>
                    <input type="text" class="form-control" value="{{ $this->razon_social_comensal }}" disabled>
                </div>
                <div class="mb-1">
                    <label for="">Código Postal:</label>
                    <input type="text" class="form-control" value="{{ $this->codigo_postal_comensal }}" disabled>
                </div>
                @endif
            </div>
            <div class="mb-3 border-bottom text-center">
                <button type="button" class="btn btn-primary mb-3 @if(!$rfc_exists) disabled @endif"
                    wire:click="facturar">Facturar</button>
            </div>
        </div>

        <div class="modal {{$registrarComensalClass}}" id="modal-comensal">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $comensal['id'] ? "Detalles Cliente" : "Nuevo Cliente" }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            wire:click="$set('registrarComensalClass', '')"></button>
                    </div>
                    <div class="modal-body pb-0">
                        <div class="row">
                            @if($alertaRegistrarComensal)
                            <div class="col-sm-12 mb-2">
                                <div class="alert alert-info">
                                    {{ $alertaRegistrarComensal }}
                                </div>
                            </div>
                            @endif
                            <div class="row mb-3">
                                <div class="col-sm-2">
                                    <x-input label="RFC" model="comensal.rfc" disabled />
                                </div>
                                <div class="col-sm-5">
                                    <x-input label="Nombre Comercial" type="text" model="comensal.nombre_comercial" />
                                </div>
                                <div class="col-sm-5">
                                    <x-input label="Razón Social" type="text" model="comensal.razon_social" />
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <x-input label="Correo" model="comensal.correo" />
                                </div>
                                <div class="col-sm-4">
                                    <x-input label="Teléfono" model="comensal.telefono" />
                                </div>
                                <div class="col-sm-4">
                                    <x-select2-modals label="Régimen Fiscal" :options="$regimenesFiscales"
                                        model="comensal.regimen_fiscal_id" class="form-control" />
                                </div>
                            </div>
                            <div wire:init="init" class="row">
                                <div class="col-12">
                                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                                        <li class="nav-item @error('comensal.direccion_fiscal.codigo_postal') text-danger fw-bold @endif"
                                            role="presentation">
                                            <button wire:ignore.self
                                                class="nav-link active"
                                                id="direccion-fiscal-tab" data-bs-toggle="tab"
                                                data-bs-target="#direccion-fiscal-tab-pane"
                                                type="button" role="tab" aria-controls="direccion-fiscal-tab-pane"
                                                aria-selected="false">
                                                @error('comensal.direccion_fiscal.codigo_postal') <i
                                                    class="bi bi-exclamation-triangle"></i> @endif
                                                Dirección Fiscal
                                            </button>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="myTabContent">
                                        <div wire:ignore.self class="tab-pane fade pt-2 show active" id="direccion-fiscal-tab-pane"
                                            role="tabpanel"
                                            aria-labelledby="direccion-fiscal-tab"
                                            tabindex="2">
                                            <div class="row">
                                                <div class="col-3">
                                                    <x-input label="Calle" type="text" model="comensal.direccion_fiscal.calle" />
                                                </div>
                                                <div class="col-3">
                                                    <x-input label="No. Exterior" type="text" model="comensal.direccion_fiscal.no_exterior" />
                                                </div>
                                                <div class="col-3">
                                                    <x-input label="No. Interior" type="text" model="comensal.direccion_fiscal.no_interior" />
                                                </div>
                                                <div class="col-3">
                                                    <x-input label="Código Postal" type="text"
                                                        model="comensal.direccion_fiscal.codigo_postal" />
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-3">
                                                    <x-input label="Colonia" type="text" model="comensal.direccion_fiscal.colonia" />
                                                </div>
                                                <div class="col-3">
                                                    <x-select2-ajax-modals label="Estado" placeholder="Seleccione..."
                                                        class="form-control"
                                                        url="{{route('estados.load-estados')}}"
                                                        model="comensal.direccion_fiscal.estado_id"
                                                        :dynamic="true" />
                                                </div>
                                                <div class="col-3">
                                                    <x-select2-ajax-modals label="Localidad" placeholder="Seleccione..."
                                                        class="form-control"
                                                        url="{{route('localidades.load-localidades',['estado_id' => $comensal['direccion_fiscal']['estado_id']])}}"
                                                        model="comensal.direccion_fiscal.localidad_id"
                                                        :dynamic="true" />
                                                </div>
                                                <div class="col-3">
                                                    <x-select2-ajax-modals label="Municipio" placeholder="Seleccione..."
                                                        class="form-control"
                                                        url="{{route('municipios.load-municipios',['estado_id' => $comensal['direccion_fiscal']['estado_id']])}}"
                                                        model="comensal.direccion_fiscal.municipio_id"
                                                        :dynamic="true" />
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <x-input label="Referencia" type="text" model="comensal.direccion_fiscal.referencia" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            wire:click="$set('registrarComensalClass', '')">{{ __('Cerrar') }}</button>
                        @if(!$comensal['id'])
                        <button type="button" class="btn btn-primary"
                            wire:click="guardarComensal">Guardar</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="modal {{$ticketImageClass}}" id="modal-comensal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ticket de Muestra</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            wire:click="$set('ticketImageClass', '')"></button>
                    </div>
                    <div class="modal-body pb-0 text-center">
                        <img src="{{ asset('/img/Ticket.png') }}" class="m-auto w-100" alt="Ticket Muestra">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            wire:click="$set('ticketImageClass', '')">{{ __('Cerrar') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
@endpush
