<x-modal form-action="save">
    <x-slot:title>
        {{$cliente->exists ? 'Editar ' : 'Crear '}}Cliente
    </x-slot:title>

    <x-slot:content>
        <div class="row mb-3">
            <div class="col-sm-5">
                <x-input label="Nombre Comercial" type="text" model="nombre_comercial"/>
            </div>
            <div class="col-sm-5">
                <x-input label="Razón Social" type="text" model="razon_social"/>
            </div>
            <div class="col-sm-2">
                <x-input label="RFC" model="rfc"/>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-sm-4">
                <x-input label="Correo" model="correo"/>
            </div>
            <div class="col-sm-4">
                <x-input label="Teléfono" model="telefono"/>
            </div>
            <div class="col-sm-4">
                <x-select2-component-modals label="Régimen Fiscal" :options="$regimenesFiscales"
                                            model="regimen_fiscal_id" class="form-control"/>
            </div>
        </div>
        <div wire:init="init" class="row">
            <div class="col-12">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item @error('direccion_fiscal.codigo_postal') text-danger fw-bold @endif"
                        role="presentation">
                        <button wire:ignore.self
                                class="nav-link active"
                                id="direccion-fiscal-tab" data-bs-toggle="tab"
                                data-bs-target="#direccion-fiscal-tab-pane"
                                type="button" role="tab" aria-controls="direccion-fiscal-tab-pane"
                                aria-selected="false">
                            @error('direccion_fiscal.codigo_postal') <i
                                class="bi bi-exclamation-triangle"></i> @endif
                            Dirección Fiscal
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button wire:ignore.self class="nav-link" id="comentario-tab" data-bs-toggle="tab"
                                data-bs-target="#comentario-tab-pane"
                                type="button" role="tab" aria-controls="comentario-tab-pane" aria-selected="false">
                            Comentarios
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
                                <x-input label="Calle" type="text" model="direccion_fiscal.calle"/>
                            </div>
                            <div class="col-3">
                                <x-input label="No. Exterior" type="text" model="direccion_fiscal.no_exterior"/>
                            </div>
                            <div class="col-3">
                                <x-input label="No. Interior" type="text" model="direccion_fiscal.no_interior"/>
                            </div>
                            <div class="col-3">
                                <x-input label="Código Postal" type="text"
                                         model="direccion_fiscal.codigo_postal"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-3">
                                <x-input label="Colonia" type="text" model="direccion_fiscal.colonia"/>
                            </div>
                            <div class="col-3">
                                <x-select2-ajax-component-modals label="Estado" placeholder="Seleccione..."
                                                                 class="form-control"
                                                                 url="{{route('estados.load-estados')}}"
                                                                 model="direccion_fiscal.estado_id"
                                                                 :dynamic="true"/>
                            </div>
                            <div class="col-3">
                                <x-select2-ajax-component-modals label="Localidad" placeholder="Seleccione..."
                                                                 class="form-control"
                                                                 url="{{route('localidades.load-localidades',['estado_id' => $direccion_fiscal['estado_id']])}}"
                                                                 model="direccion_fiscal.localidad_id"
                                                                 :dynamic="true"/>
                            </div>
                            <div class="col-3">
                                <x-select2-ajax-component-modals label="Municipio" placeholder="Seleccione..."
                                                                 class="form-control"
                                                                 url="{{route('municipios.load-municipios',['estado_id' => $direccion_fiscal['estado_id']])}}"
                                                                 model="direccion_fiscal.municipio_id"
                                                                 :dynamic="true"/>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <x-input label="Referencia" type="text" model="direccion_fiscal.referencia"/>
                            </div>
                        </div>
                    </div>
                    <div wire:ignore.self class="tab-pane fade pt-2" id="comentario-tab-pane" role="tabpanel"
                         aria-labelledby="comentario-tab"
                         tabindex="6">
                        <div class="row">
                            <div class="col-12">
                                <x-textarea class="form-control" model="comentarios" placeholder="Comentarios..."
                                            rows="5"></x-textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">
            Cerrar
        </button>
        <button type="submit" class="btn btn-primary">Guardar Cliente</button>
    </x-slot:buttons>
</x-modal>
