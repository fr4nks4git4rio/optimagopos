@section('title', 'Cabecera Facturas')

<div>
    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row">
        <fieldset>
            <legend>Datos Generales</legend>
            <hr>
            <div class="row">
                <div class="col-sm-5">
                    <x-input label="Nombre Comercial" type="text"
                        model="owner.nombre_comercial" />
                </div>
                <div class="col-sm-5">
                    <x-input label="Razón Social" type="text"
                        model="owner.razon_social" />
                </div>
                <div class="col-sm-2">
                    <x-input label="RFC" type="text" model="owner.rfc"
                        class="text-uppercase" />
                </div>
            </div>
            <div class="row">
                <div class="col-sm-5">
                    <x-select2 :label="'Régimen Fiscal'" :placeholder="'Seleccione...'"
                        :options="$regimenesFiscales"
                        model="owner.regimen_fiscal_id"
                        class="form-control" />
                </div>
                <div class="col-sm-5">
                    <x-input label="Correo" type="email"
                        model="owner.correo" />
                </div>
                <div class="col-sm-2">
                    <x-input label="Teléfono" type="text"
                        model="owner.telefono" />
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="mb-1">
                        <label for="">Portal del PAC</label>
                        <textarea rows="2" class="form-control @error('owner.portal_pac') is-invalid @enderror"
                            wire:model="owner.portal_pac"></textarea>
                        @error('owner.portal_pac')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <x-input label="Usuario Integrador SAT" type="text"
                        model="owner.usuario_integrador_sat" />
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>Dirección Fiscal</legend>
            <hr>
            <div class="row">
                <div class="col-sm-3">
                    <x-input label="Calle" model="owner.direccion.calle" />
                </div>
                <div class="col-sm-3">
                    <x-input label="No. Exterior"
                        model="owner.direccion.no_exterior" />
                </div>
                <div class="col-sm-3">
                    <x-input label="No. Interior"
                        model="owner.direccion.no_interior" />
                </div>
                <div class="col-sm-3">
                    <x-input label="Código Postal"
                        model="owner.direccion.codigo_postal" />
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3">
                    <x-input label="Colonia" model="owner.direccion.colonia" />
                </div>
                <div class="col-sm-3">
                    <x-select2-ajax label="Estado" placeholder="Seleccione..."
                        class="form-control"
                        url="{{route('estados.load-estados')}}"
                        model="owner.direccion.estado_id"
                        :dynamic="true" />
                </div>
                <div class="col-sm-3">
                    <x-select2-ajax label="Localidad" placeholder="Seleccione..."
                        class="form-control"
                        url="{{route('localidades.load-localidades', ['estado_id' => $owner['direccion']['estado_id']])}}"
                        model="owner.direccion.localidad_id"
                        :dynamic="true" />
                </div>
                <div class="col-sm-3">
                    <x-select2-ajax label="Municipio" placeholder="Seleccione..."
                        class="form-control"
                        url="{{route('municipios.load-municipios', ['estado_id' => $owner['direccion']['estado_id']])}}"
                        model="owner.direccion.municipio_id"
                        :dynamic="true" />
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <x-input label="Referencia"
                        model="owners.direccion.referencia" />
                </div>
            </div>
        </fieldset>
        <div class="text-center mt-3">
            <button class="btn btn-primary w-auto float-end"
                wire:click="saveDatosGenerales()">Guardar Datos
            </button>
        </div>
    </div>
</div>
