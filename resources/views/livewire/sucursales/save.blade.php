<x-modal form-action="save">
    <x-slot:title>
        {{ $sucursal->exists ? 'Editar ' : 'Crear ' }}Sucursal
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init" class="row">
            <div x-data="{ logo_uploaded: false }" class="col-12 col-md-3 text-center mb-2"
                x-on:livewire-upload-finish="logo_uploaded=true;$wire.logo_src = URL.createObjectURL(document.getElementById('logo').files[0])">
                <label for="">Logo</label>
                <hr>
                @if (!$this->has_logo)
                    <img src="{{ asset('img/no_image.png') }}" alt="" class="img-thumbnail rounded-4"
                        id="logo_image">
                @else
                    <template x-if="logo_uploaded">
                        <img src="{{ $logo_src }}" alt="" class="img-thumbnail rounded-4">
                    </template>
                    <template x-if="!logo_uploaded">
                        <img src="{{ asset($logo_src) }}" alt="" class="img-thumbnail rounded-4">
                    </template>
                @endif

                <input type="file" style="display: none" id="logo" wire:model="logo" accept=".jpg,.jpeg,.png">
                <button type="button" class="btn btn-site-primary mt-2"
                    onclick="document.getElementById('logo').click()">Subir Logo
                </button>
                @if ($this->has_logo)
                    <button type="button" class="btn btn-secondary mt-2" wire:click="removeLogo()">Quitar Logo
                    </button>
                @endif
            </div>
            <div class="col-12 col-md-9">
                @if (user()->is_super_admin)
                    <div class="row mb-3">
                        <div class="col-md-6 col-12">
                            @if ($from_subscription)
                                <x-select2-ajax-component-modals label="Cliente" placeholder="Seleccione..."
                                    class="form-control" url="{{ route('clientes.load-clientes') }}" model="cliente_id"
                                    :dynamic="true" disabled />
                            @else
                                <x-select2-ajax-component-modals label="Cliente" placeholder="Seleccione..."
                                    class="form-control" url="{{ route('clientes.load-clientes') }}" model="cliente_id"
                                    :dynamic="true" />
                            @endif
                        </div>
                        @if (!$from_subscription)
                            <div class="col-md-6 col-12">
                                <x-select2-component-modals label="Suscripción" placeholder="Seleccione..."
                                    class="form-control" :options="$suscripciones" model="suscripcion_id" :dynamic="true" />
                            </div>
                        @endif
                    </div>
                @endif
                <div class="row mb-2">
                    <div class="col-md-6 col-12">
                        <input type="file" style="display: none" accept=".pdf" id="file_constacia_fiscal"
                            wire:model="constancia_fiscal">
                        <button type="button" class="btn btn-warning" wire:loading.attr="disabled"
                            onclick="document.getElementById('file_constacia_fiscal').click()">Cargar datos fiscales
                            desde
                            Constancia Fiscal</button>
                    </div>
                    @if ($this->con_facturacion)
                        <div class="col-md-6 col-12">
                            <x-toggle-button :lazy="true" :label="'Tomar datos fiscales de empresa matriz'" :inline="true"
                                model="tomar_datos_fiscales_de_empresa_matriz" />
                        </div>
                    @endif
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <x-input label="Nombre Comercial" type="text" model="nombre_comercial" />
                    </div>
                    <div class="col-sm-5">
                        @if ($tomar_datos_fiscales_de_empresa_matriz)
                            <x-input label="Razón Social" type="text" model="razon_social" disabled />
                        @else
                            <x-input label="Razón Social" type="text" model="razon_social" />
                        @endif
                    </div>
                    <div class="col-sm-3">
                        @if ($tomar_datos_fiscales_de_empresa_matriz)
                            <x-input label="RFC" model="rfc" disabled />
                        @else
                            <x-input label="RFC" model="rfc" />
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <x-input label="Correo" model="correo" />
                    </div>
                    <div class="col-sm-4">
                        <x-input label="Teléfono" model="telefono" />
                    </div>
                    <div class="col-sm-4">
                        <x-select2-component-modals label="Régimen Fiscal" :options="$regimenesFiscales" model="regimen_fiscal_id"
                            class="form-control" :dynamic="true" />
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <div class="mb-1">
                            <label for="">Vigencia de tickets para facturación:</label>
                            <select
                                class="form-control  @error('tipo_vigencia_ticket_facturacion') is-invalid @enderror"
                                wire:model="tipo_vigencia_ticket_facturacion">
                                <option value="">Seleccione...</option>
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
                            <x-input model="dias_vigencia" type="number" label="Cantidad de días" />
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
                                    Dirección Fiscal
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
                                            <x-input label="Calle" type="text" model="direccion_fiscal.calle" />
                                        </div>
                                        <div class="col-3">
                                            <x-input label="No. Exterior" type="text"
                                                model="direccion_fiscal.no_exterior" />
                                        </div>
                                        <div class="col-3">
                                            <x-input label="No. Interior" type="text"
                                                model="direccion_fiscal.no_interior" />
                                        </div>
                                        <div class="col-3">
                                            <x-input label="Código Postal" type="text"
                                                model="direccion_fiscal.codigo_postal" />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-3">
                                            <x-input label="Colonia" type="text"
                                                model="direccion_fiscal.colonia" />
                                        </div>
                                        <div class="col-3">
                                            <x-select2-ajax-component-modals label="Estado"
                                                placeholder="Seleccione..." class="form-control"
                                                url="{{ route('estados.load-estados') }}"
                                                model="direccion_fiscal.estado_id" :dynamic="true" />
                                        </div>
                                        <div class="col-3">
                                            <x-select2-ajax-component-modals label="Localidad"
                                                placeholder="Seleccione..." class="form-control"
                                                url="{{ route('localidades.load-localidades', ['estado_id' => $direccion_fiscal['estado_id']]) }}"
                                                model="direccion_fiscal.localidad_id" :dynamic="true" />
                                        </div>
                                        <div class="col-3">
                                            <x-select2-ajax-component-modals label="Municipio"
                                                placeholder="Seleccione..." class="form-control"
                                                url="{{ route('municipios.load-municipios', ['estado_id' => $direccion_fiscal['estado_id']]) }}"
                                                model="direccion_fiscal.municipio_id" :dynamic="true" />
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <x-input label="Referencia" type="text"
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
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">
            Cerrar
        </button>
        <button type="submit" class="btn btn-primary">Guardar Sucursal</button>
    </x-slot:buttons>
</x-modal>
