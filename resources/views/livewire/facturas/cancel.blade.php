<x-modal form-action="cancel">
    <div wire:loading.delay.longer>
        <div class="loading">
            <img src="{{asset('img/loading.gif')}}"/>
        </div>
    </div>
    <x-slot:title>
        Cancelar {{$this->type}}
    </x-slot:title>

    <x-slot:content>
        <div class="row">
            <div class="col-sm-12">
                <x-alert icon="exclamation-octagon" alert="danger">
                    {{$this->text_alert}} en <strong>MODO: {{$this->modo}}</strong>
                </x-alert>
            </div>
            <div class="col-sm-12 mb-3">
                <x-select2-component-modals :label="'Motivo de Cancelación'" class="form-control" :options="$motivos" model="motivo"/>
            </div>
            @if($this->mostrar_sustitutos)
                <div class="col-sm-12">
                    <x-select2-component-modals :label="'Folio Sustituto'" class="form-control" :options="$facturas" model="factura_sustituta"/>
                </div>
            @endif
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">
            Cerrar
        </button>
        <button type="submit" class="btn btn-danger">Cancelar {{$this->type}}</button>
    </x-slot:buttons>
</x-modal>
