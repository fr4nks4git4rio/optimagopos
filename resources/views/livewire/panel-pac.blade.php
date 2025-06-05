<x-modal form-action="save">
    <x-slot:title>
        Panel PAC
    </x-slot:title>

    <x-slot:content>
        <div
            x-data='{
                    buscarTimbresDisponibles(rfc){
                        $.ajax({
                        dataType: "json",
                        url: "/obtener-timbres-disponibles/" + rfc,
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
                <legend>Modo de Timbrado</legend>
                <hr>
                <div class="text-center pt-3">
                    <label class="px-3">
                        <input type="radio" name="modo" wire:model="cfdi_timbrado_productivo"
                            @click="setTimeout(() => {buscarTimbresDisponibles('{{ $owner['rfc'] }}')}, 500)" value="0"
                            wire:click="changeTimbrado"> Prueba
                    </label>
                    <label class="px-3">
                        <input type="radio" name="modo" wire:model="cfdi_timbrado_productivo"
                            @click="setTimeout(() => {buscarTimbresDisponibles('{{ $owner['rfc'] }}')}, 500)" value="1"
                            wire:click="changeTimbrado"> Producción
                    </label>
                </div>
            </fieldset>
            <div class="col-sm-12 mt-3">
                <div class="row py-3" x-data="{}"
                    x-init='
                        $(document).ready(function () {
                            buscarTimbresDisponibles("{{$owner['rfc']}}");
                        });'>
                    <fieldset>
                        <legend>Revisar Facturas</legend>
                        <hr>
                        <div class="text-center mt-3">
                            <a href="{{$owner['portal_pac']}}"
                                target="_blank"
                                class="btn btn-outline-danger fw-bold" type="button">Visitar portal del
                                PAC</a>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>Timbres Disponibles</legend>
                        <hr>
                        <div>
                            <div class="input-group col-sm-12">
                                <span class="input-group-text">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </span>
                                <input type="text" class="form-control {{$owner['rfc']}}"
                                    style="padding-left: 30px; width: 400px !important;"
                                    value="Obteniendo Información"
                                    width="100%">
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>
        </div>

    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">
            Cerrar
        </button>
    </x-slot:buttons>
</x-modal>
