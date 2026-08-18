<x-modal form-action="save">
    <x-slot:title>
        {{ __('site.panel_pac.title') }}
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init" class="row">
            <ul class="nav nav-tabs" id="myTabPanelPac" role="tablist">
                @foreach ($sucursales as $index => $sucursal)
                    <li class="nav-item" role="presentation">
                        <button wire:ignore.self class="nav-link @if ($index == 0) active @endif"
                            id="suc-{{ $index }}-tab" data-bs-toggle="tab"
                            data-bs-target="#suc-{{ $index }}-tab-pane" type="button" role="tab"
                            aria-controls="suc-{{ $index }}-tab-pane"
                            aria-selected="true">{{ $sucursal['nombre_comercial'] }}</button>
                    </li>
                @endforeach
            </ul>
            <div class="tab-content" id="myTabContentPanelPac">
                @foreach ($sucursales as $index => $sucursal)
                    <div wire:ignore.self class="tab-pane fade @if ($index == 0) show active @endif"
                        id="suc-{{ $index }}-tab-pane" role="tabpanel"
                        aria-labelledby="suc-{{ $index }}-tab" tabindex="0">
                        <div
                            x-data='{
                    buscarTimbresDisponibles(rfc){
                        $.ajax({
                        dataType: "json",
                        url: "/cliente/obtener-timbres-disponibles/" + rfc,
                        success: function (data){
                            $(".input-group-text").css("display", "none");
                            if (data.success){
                                $("div.input-group.col-sm-12 > input."+rfc).addClass("text-success-dark");
                                $("div.input-group.col-sm-12 > input."+rfc).val(data.disponibles);
                            }else{
                                $("div.input-group.col-sm-12 > input."+rfc).addClass("text-danger-dark");
                                if (data.message){
                                    $("div.input-group.col-sm-12 > input."+rfc).val(data.message);
                                }else{
                                    $("div.input-group.col-sm-12 > input."+rfc).val("Error Obteniendo la Información.");
                                    $wire.emit("show-toast", data.slice(0, data.indexOf("}") + 1), "danger");
                                }
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown){
                            $(".input-group-text").css("display", "none");
                            $("div.input-group.col-sm-12 > input."+rfc).addClass("text-danger-dark");
                            $("div.input-group.col-sm-12 > input."+rfc).val("Error Obteniendo la Información.");
                            $wire.emit("show-toast", errorThrown, "danger");
                        }
                        });
                    }
                }'>
                            <fieldset>
                                <legend>{{ __('site.panel_pac.stamping_method') }}</legend>
                                <hr>
                                <div class="text-center pt-3">
                                    <x-toggle-button :label="__('site.panel_pac.production_method')" :onChange="'changeTimbrado'" :index="$index"
                                        :inline="true"
                                        model="sucursales.{{ $index }}.cfdi_timbrado_productivo" />
                                </div>
                            </fieldset>
                            <div class="col-sm-12 mt-3">
                                <div class="row py-3" x-data="{}"
                                    x-init='
                        $(document).ready(function () {
                            buscarTimbresDisponibles("{{ $sucursal['rfc'] }}");
                        });'>
                                    <fieldset>
                                        <legend>{{ __('site.panel_pac.review_invoices') }}</legend>
                                        <hr>
                                        <div class="text-center mt-3">
                                            <a href="{{ $sucursal['portal_pac'] }}" target="_blank"
                                                class="btn btn-outline-danger fw-bold" type="button">
                                                {{ __('site.panel_pac.visit_panel_pac_site') }}
                                            </a>
                                        </div>
                                    </fieldset>
                                    <fieldset>
                                        <legend>{{ __('site.panel_pac.available_stamps') }}</legend>
                                        <hr>
                                        <div>
                                            <div class="input-group col-sm-12">
                                                <span class="input-group-text">
                                                    <div class="spinner-border spinner-border-sm" role="status">
                                                        <span class="visually-hidden">{{ __('site.common.loading') }}...</span>
                                                    </div>
                                                </span>
                                                <input type="text" class="form-control {{ $sucursal['rfc'] }}"
                                                    style="padding-left: 30px; width: 400px !important;"
                                                    value="{{ __('site.common.getting_information') }}" width="100%">
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$dispatch('closeModal')">
            {{ __('site.common.close') }}
        </button>
    </x-slot:buttons>
</x-modal>
