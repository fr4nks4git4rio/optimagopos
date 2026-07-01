@section('title', 'Cabecera Factura')

<div wire:init="init">
    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row py-2">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="fs-5 fw-bold">Datos Generales</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-5">
                            <x-input label="Nombre Comercial" type="text" model="nombre_comercial" />
                        </div>
                        <div class="col-sm-5">
                            <x-input label="Razón Social" type="text" model="razon_social" />
                        </div>
                        <div class="col-sm-2">
                            <x-input label="RFC" type="text" model="rfc" class="text-uppercase" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <x-select2 :label="'Régimen Fiscal'" :placeholder="'Seleccione...'" :options="$regimenesFiscales" model="regimen_fiscal_id"
                                class="form-control" />
                        </div>
                        <div class="col-sm-4">
                            <x-input label="Correo" type="email" model="correo" />
                        </div>
                        <div class="col-sm-2">
                            <x-input label="Teléfono" type="text" model="telefono" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="mb-1">
                                <label for="">Portal del PAC</label>
                                <textarea rows="2" class="form-control @error('portal_pac') is-invalid @enderror" wire:model="portal_pac"></textarea>
                                @error('portal_pac')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <x-input label="Usuario Integrador SAT" type="text" model="usuario_integrador_sat" />
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="text-center">
                        <button class="btn btn-primary w-auto float-end" wire:click="saveDatosGenerales">Guardar
                            Datos
                        </button>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h3 class="fs-5 fw-bold">Dirección Fiscal</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-3">
                            <x-input label="Calle" model="direccion.calle" />
                        </div>
                        <div class="col-sm-3">
                            <x-input label="No. Exterior" model="direccion.no_exterior" />
                        </div>
                        <div class="col-sm-3">
                            <x-input label="No. Interior" model="direccion.no_interior" />
                        </div>
                        <div class="col-sm-3">
                            <x-input label="Código Postal" model="direccion.codigo_postal" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <x-input label="Colonia" model="direccion.colonia" />
                        </div>
                        <div class="col-sm-3">
                            <x-select2-ajax label="Estado" placeholder="Seleccione..." class="form-control"
                                url="{{ route('estados.load-estados') }}" model="direccion.estado_id"
                                :dynamic="true" />
                        </div>
                        <div class="col-sm-3">
                            <x-select2-ajax label="Localidad" placeholder="Seleccione..." class="form-control"
                                url="{{ route('localidades.load-localidades', ['estado_id' => $direccion['estado_id']]) }}"
                                model="direccion.localidad_id" :dynamic="true" />
                        </div>
                        <div class="col-sm-3">
                            <x-select2-ajax label="Municipio" placeholder="Seleccione..." class="form-control"
                                url="{{ route('municipios.load-municipios', ['estado_id' => $direccion['localidad_id']]) }}"
                                model="direccion.municipio_id" :dynamic="true" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <x-input label="Referencia" model="direccion.referencia" />
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="text-center">
                        <button class="btn btn-primary w-auto float-end" wire:click="saveDireccion">Guardar
                            Datos
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
