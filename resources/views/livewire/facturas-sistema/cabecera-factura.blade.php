@section('title', __('site.invoice_header.title'))

<div wire:init="init">
    <h1 class="fs-1 mb-2">@yield('title')</h1>

    <div class="row py-2">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="fs-5 fw-bold">{{ __('site.invoice_header.general_data') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-5">
                            <x-input label="{{ __('site.invoice_header.commercial_name') }}" type="text" model="nombre_comercial" />
                        </div>
                        <div class="col-sm-5">
                            <x-input label="{{ __('site.invoice_header.social_reason') }}" type="text" model="razon_social" />
                        </div>
                        <div class="col-sm-2">
                            <x-input label="{{ __('site.invoice_header.rfc') }}" type="text" model="rfc" class="text-uppercase" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <x-select2 label="{{ __('site.invoice_header.fiscal_regime') }}" placeholder="{{ __('site.common.select') }}..." :options="$regimenesFiscales" model="regimen_fiscal_id"
                                class="form-control" />
                        </div>
                        <div class="col-sm-4">
                            <x-input label="{{ __('site.invoice_header.email') }}" type="email" model="correo" />
                        </div>
                        <div class="col-sm-2">
                            <x-input label="{{ __('site.invoice_header.phone') }}" type="text" model="telefono" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="mb-1">
                                <label for="">{{ __('site.invoice_header.portal_pac') }}</label>
                                <textarea rows="2" class="form-control @error('portal_pac') is-invalid @enderror" wire:model="portal_pac"></textarea>
                                @error('portal_pac')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <x-input label="{{ __('site.invoice_header.integrator_user_sat') }}" type="text" model="usuario_integrador_sat" />
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="text-center">
                        <button class="btn btn-primary w-auto float-end" wire:click="saveDatosGenerales">
                            {{ __('site.invoice_header.save_data') }}
                        </button>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h3 class="fs-5 fw-bold">{{ __('site.invoice_header.fiscal_address') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-3">
                            <x-input label="{{ __('site.address.street') }}" model="direccion.calle" />
                        </div>
                        <div class="col-sm-3">
                            <x-input label="{{ __('site.address.exterior_number') }}" model="direccion.no_exterior" />
                        </div>
                        <div class="col-sm-3">
                            <x-input label="{{ __('site.address.interior_number') }}" model="direccion.no_interior" />
                        </div>
                        <div class="col-sm-3">
                            <x-input label="{{ __('site.address.postal_code') }}" model="direccion.codigo_postal" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <x-input label="{{ __('site.address.colony') }}" model="direccion.colonia" />
                        </div>
                        <div class="col-sm-3">
                            <x-select2-ajax label="{{ __('site.address.state') }}" placeholder="{{ __('site.ammon.select') }}..." class="form-control"
                                url="{{ route('estados.load-estados') }}" model="direccion.estado_id"
                                :dynamic="true" />
                        </div>
                        <div class="col-sm-3">
                            <x-select2-ajax label="{{ __('site.address.locality') }}" placeholder="{{ __('site.common.select') }}..." class="form-control"
                                url="{{ route('localidades.load-localidades', ['estado_id' => $direccion['estado_id']]) }}"
                                model="direccion.localidad_id" :dynamic="true" />
                        </div>
                        <div class="col-sm-3">
                            <x-select2-ajax label="{{ __('site.address.municipality') }}" placeholder="{{ __('site.common.select') }}..." class="form-control"
                                url="{{ route('municipios.load-municipios', ['estado_id' => $direccion['localidad_id']]) }}"
                                model="direccion.municipio_id" :dynamic="true" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <x-input label="{{ __('site.address.reference') }}" model="direccion.referencia" />
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="text-center">
                        <button class="btn btn-primary w-auto float-end" wire:click="saveDireccion">
                            {{ __('site.invoice_header.save_data') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
