<x-modal form-action="save">
    <x-slot:title>
        {{ $comensal->id ? __('site.diners.save.edit_diner') : __('site.diners.save.create_diner') }}
    </x-slot:title>

    <x-slot:content>
        <div class="row mb-1">
            <x-toggle-button label="{{ __('site.diners.save.include_billing') }}" :inline="true" :lazy="true" model="con_facturacion" />
            @if ($con_facturacion)
                <div class="mb-3">
                    <input type="file" style="display: none" accept=".pdf" id="file_constacia_fiscal"
                        wire:model="constancia_fiscal">
                    <button type="button" class="btn btn-warning" wire:loading.attr="disabled"
                        onclick="document.getElementById('file_constacia_fiscal').click()">
                        {{ __('site.diners.save.load_fiscal_data') }}
                    </button>
                </div>
            @endif
        </div>
        <div class="row mb-3">
            <div class="col-sm-5">
                <x-input label="{{ __('site.diners.save.commercial_name') }}" type="text" model="nombre_comercial" />
            </div>
            <div class="col-sm-5">
                <x-input label="{{ __('site.diners.save.social_reason') }}" type="text" model="razon_social" />
            </div>
            <div class="col-sm-2">
                <x-input label="{{ __('site.diners.save.rfc') }}" model="rfc" />
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-sm-4">
                <x-input label="{{ __('site.diners.save.email') }}" model="correo" />
            </div>
            <div class="col-sm-4">
                <x-input label="{{ __('site.diners.save.phone') }}" model="telefono" />
            </div>
            <div class="col-sm-4">
                <x-select2-component-modals label="{{ __('site.diners.save.fiscal_regime') }}" :options="$regimenesFiscales" model="regimen_fiscal_id"
                    class="form-control" />
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
                            {{ __('site.diners.save.fiscal_address') }}
                        </button>
                    </li>
                    <li class="nav-item"
                        role="presentation">
                        <button wire:ignore.self class="nav-link" id="comentario-tab" data-bs-toggle="tab"
                            data-bs-target="#comentario-tab-pane" type="button" role="tab"
                            aria-controls="comentario-tab-pane" aria-selected="false">
                            {{ __('site.diners.save.comments') }}
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div wire:ignore.self class="tab-pane fade pt-2 show active" id="direccion-fiscal-tab-pane"
                        role="tabpanel" aria-labelledby="direccion-fiscal-tab" tabindex="2">
                        <div class="row">
                            <div class="col-3">
                                <x-input label="{{ __('site.address.street') }}" type="text" model="direccion_fiscal.calle" />
                            </div>
                            <div class="col-3">
                                <x-input label="{{ __('site.address.exterior_number') }}" type="text" model="direccion_fiscal.no_exterior" />
                            </div>
                            <div class="col-3">
                                <x-input label="{{ __('site.address.interior_number') }}" type="text" model="direccion_fiscal.no_interior" />
                            </div>
                            <div class="col-3">
                                <x-input label="{{ __('site.address.postal_code') }}" type="text" model="direccion_fiscal.codigo_postal" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-3">
                                <x-input label="{{ __('site.address.colony') }}" type="text" model="direccion_fiscal.colonia" />
                            </div>
                            <div class="col-3">
                                <x-select2-ajax-component-modals label="{{ __('site.address.state') }}" placeholder="Seleccione..."
                                    class="form-control" url="{{ route('estados.load-estados') }}"
                                    model="direccion_fiscal.estado_id" :dynamic="true" />
                            </div>
                            <div class="col-3">
                                <x-select2-ajax-component-modals label="{{ __('site.address.locality') }}" placeholder="Seleccione..."
                                    class="form-control"
                                    url="{{ route('localidades.load-localidades', ['estado_id' => $direccion_fiscal['estado_id']]) }}"
                                    model="direccion_fiscal.localidad_id" :dynamic="true" />
                            </div>
                            <div class="col-3">
                                <x-select2-ajax-component-modals label="{{ __('site.address.municipality') }}" placeholder="Seleccione..."
                                    class="form-control"
                                    url="{{ route('municipios.load-municipios', ['estado_id' => $direccion_fiscal['estado_id']]) }}"
                                    model="direccion_fiscal.municipio_id" :dynamic="true" />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <x-input label="{{ __('site.address.reference') }}" type="text" model="direccion_fiscal.referencia" />
                            </div>
                        </div>
                    </div>
                    <div wire:ignore.self class="tab-pane fade pt-2" id="comentario-tab-pane" role="tabpanel"
                        aria-labelledby="comentario-tab" tabindex="6">
                        <div class="row">
                            <div class="col-12">
                                <x-textarea class="form-control" model="comentarios" placeholder="{{ __('site.diners.save.comments') }}..."
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
            {{ __('site.common.close') }}
        </button>
        <button type="submit" class="btn btn-primary">
            {{ __('site.diners.save.save_diner') }}
        </button>
    </x-slot:buttons>
</x-modal>
