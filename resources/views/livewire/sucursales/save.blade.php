<x-modal form-action="save">
    <x-slot:title>
        {{ $sucursal->id ? __('site.branches.save.edit_branch') : __('site.branches.save.create_branch') }}
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init" class="row">
            <div class="col-12 col-md-3 text-center mb-2">
                <label for="">{{ __('site.branches.save.logo') }}</label>
                <hr>
                {{-- 1. Previsualización cuando hay un archivo recién subido --}}
                @if ($logo)
                    <img src="{{ $logo->temporaryUrl() }}" alt="Logo preview" class="img-thumbnail rounded-4">

                    {{-- 2. Mostrar logo existente de la base de datos si existe --}}
                @elseif ($logo_src)
                    <img src="{{ asset($logo_src) }}" alt="Logo actual" class="img-thumbnail rounded-4">

                    {{-- 3. Imagen por defecto si no hay nada --}}
                @else
                    <img src="{{ asset('img/no_image.png') }}" alt="Sin imagen" class="img-thumbnail rounded-4">
                @endif

                {{-- Input oculto --}}
                <input type="file" id="logo" class="d-none" wire:model="logo" accept=".jpg,.jpeg,.png">

                {{-- Botón para disparar la selección de archivo --}}
                <button type="button" class="btn btn-site-primary mt-2"
                    onclick="document.getElementById('logo').click()">
                    {{ __('site.branches.save.upload_logo') }}
                </button>

                {{-- Botón para remover logo --}}
                @if ($logo || $logo_src)
                    <button type="button" class="btn btn-secondary mt-2" wire:click="removeLogo">
                        {{ __('site.branches.save.remove_logo') }}
                    </button>
                @endif
            </div>
            <div class="col-12 col-md-9">
                @if (user()->is_super_admin)
                    <div class="row mb-3">
                        <div class="col-md-6 col-12">
                            @if ($from_subscription)
                                <x-select2-component-modals label="{{ __('site.branches.save.client') }}"
                                    placeholder="{{ __('site.common.select') }}..." class="form-control"
                                    :options="$clientes" model="cliente_id" :dynamic="true" disabled />
                            @else
                                <x-select2-component-modals label="{{ __('site.branches.save.client') }}"
                                    placeholder="{{ __('site.common.select') }}..." class="form-control"
                                    :options="$clientes" model="cliente_id" :dynamic="true" />
                            @endif
                        </div>
                        @if (!$from_subscription)
                            <div class="col-md-6 col-12">
                                <x-select2-component-modals label="{{ __('site.branches.save.subscription') }}"
                                    placeholder="{{ __('site.common.select') }}..." class="form-control"
                                    :options="$suscripciones" model="suscripcion_id" :dynamic="true" />
                            </div>
                        @endif
                    </div>
                @endif
                <div class="row mb-2">
                    <div class="col-md-6 col-12">
                        <input type="file" style="display: none" accept=".pdf" id="file_constacia_fiscal"
                            wire:model.live="constancia_fiscal">
                        <button type="button" class="btn btn-warning" wire:loading.attr="disabled"
                            onclick="document.getElementById('file_constacia_fiscal').click()">
                            {{ __('site.branches.save.load_fiscal_data') }}
                        </button>
                    </div>
                    @if ($this->con_facturacion)
                        <div class="col-md-6 col-12">
                            <x-toggle-button :lazy="true"
                                label="{{ __('site.branches.save.obtain_tax_data_from_company') }}" :inline="true"
                                model="tomar_datos_fiscales_de_empresa_matriz" />
                        </div>
                    @endif
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <x-input label="{{ __('site.branches.save.commercial_name') }}" type="text"
                            model="nombre_comercial" />
                    </div>
                    <div class="col-sm-5">
                        @if ($tomar_datos_fiscales_de_empresa_matriz)
                            <x-input label="{{ __('site.branches.save.social_reason') }}" type="text"
                                model="razon_social" disabled />
                        @else
                            <x-input label="{{ __('site.branches.save.social_reason') }}" type="text"
                                model="razon_social" />
                        @endif
                    </div>
                    <div class="col-sm-3">
                        @if ($tomar_datos_fiscales_de_empresa_matriz)
                            <x-input label="{{ __('site.branches.save.rfc') }}" model="rfc" disabled />
                        @else
                            <x-input label="{{ __('site.branches.save.rfc') }}" model="rfc" />
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <x-input label="{{ __('site.branches.save.email') }}" model="correo" />
                    </div>
                    <div class="col-sm-4">
                        <x-input label="{{ __('site.branches.save.phone') }}" model="telefono" />
                    </div>
                    <div class="col-sm-4">
                        <x-select2-component-modals label="{{ __('site.branches.save.fiscal_regime') }}"
                            :options="$regimenesFiscales" model="regimen_fiscal_id" class="form-control" :dynamic="true" />
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <div class="mb-1">
                            <label for="">{{ __('site.branches.save.ticket_validity_for_billing') }}:</label>
                            <select
                                class="form-control  @error('tipo_vigencia_ticket_facturacion') is-invalid @enderror"
                                wire:model.live="tipo_vigencia_ticket_facturacion">
                                <option value="">{{ __('site.common.select') }}...</option>
                                @foreach ($tiposVigenciaTicketFacturacion as $value)
                                    <option value="{{ $value }}">{{ __($value) }}</option>
                                @endforeach
                            </select>
                            @error('tipo_vigencia_ticket_facturacion')
                                <span class="invalid-feedback d-block" role="alert">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>
                    @if (in_array($tipo_vigencia_ticket_facturacion, ['days_number_after_emitted', 'days_number_next_month']))
                        <div class="col-sm-3">
                            <x-input model="dias_vigencia" type="number"
                                label="{{ __('site.branches.save.number_of_days') }}" />
                        </div>
                    @endif
                </div>
                <div class="row">
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
                                    {{ __('site.branches.save.fiscal_address') }}
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content"
                                id="myTabContent">
                                <div wire:ignore.self class="tab-pane fade pt-2 show active"
                                    id="direccion-fiscal-tab-pane" role="tabpanel"
                                    aria-labelledby="direccion-fiscal-tab" tabindex="2">
                                    <div class="row">
                                        <div class="col-3">
                                            <x-input label="{{ __('site.address.street') }}" type="text"
                                                model="direccion_fiscal.calle" />
                                        </div>
                                        <div class="col-3">
                                            <x-input label="{{ __('site.address.exterior_number') }}" type="text"
                                                model="direccion_fiscal.no_exterior" />
                                        </div>
                                        <div class="col-3">
                                            <x-input label="{{ __('site.address.interior_number') }}" type="text"
                                                model="direccion_fiscal.no_interior" />
                                        </div>
                                        <div class="col-3">
                                            <x-input label="{{ __('site.address.postal_code') }}" type="text"
                                                model="direccion_fiscal.codigo_postal" />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3">
                                            <x-input label="{{ __('site.address.colony') }}" type="text"
                                                model="direccion_fiscal.colonia" />
                                        </div>
                                        <div class="col-3">
                                            <x-select2-ajax-component-modals label="{{ __('site.address.state') }}"
                                                placeholder="{{ __('site.common.select') }}..." class="form-control"
                                                url="{{ route('estados.load-estados') }}"
                                                model="direccion_fiscal.estado_id" :dynamic="true" />
                                        </div>
                                        <div class="col-3">
                                            <x-select2-ajax-component-modals label="{{ __('site.address.locality') }}"
                                                placeholder="{{ __('site.common.select') }}..." class="form-control"
                                                url="{{ route('localidades.load-localidades', ['estado_id' => $direccion_fiscal['estado_id']]) }}"
                                                model="direccion_fiscal.localidad_id" :dynamic="true" />
                                        </div>
                                        <div class="col-3">
                                            <x-select2-ajax-component-modals label="Municipio"
                                                placeholder="{{ __('site.common.select') }}..." class="form-control"
                                                url="{{ route('municipios.load-municipios', ['estado_id' => $direccion_fiscal['estado_id']]) }}"
                                                model="direccion_fiscal.municipio_id" :dynamic="true" />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <x-input label="{{ __('site.address.reference') }}" type="text"
                                                model="direccion_fiscal.referencia" />
                                        </div>
                                    </div>
                                </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary mr-2" data-bs-dismiss="modal"
            wire:click="$dispatch('closeModal')">
            {{ __('site.common.close') }}
        </button>
        <button type="submit" class="btn btn-primary">{{ __('site.branches.save.save_branch') }}</button>
    </x-slot:buttons>
</x-modal>
