<x-modal form-action="timbrar">
    <div wire:loading>
        <div class="loading">
            <div class="spinner-border text-primary my-3" role="status"><span class="visually-hidden">Cargando...</span></div>
        </div>
    </div>
    <x-slot:title>
        {{__('site.common.stamp')}} {{$this->type}}
    </x-slot:title>

    <x-slot:content>
        <div class="row">
            <div class="col-sm-12">
                <x-alert icon="exclamation-octagon" alert="danger">
                    {!! $this->text_alert !!} en <strong class="text-uppercase" style="text-transform: uppercase">{{__('site.common.mode')}}: {{$this->modo}}</strong>
                </x-alert>
            </div>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$dispatch('closeModal')">
            {{__('site.common.close')}}
        </button>
        <button type="submit" class="btn btn-danger">{{__('site.common.stamp')}} {{$this->type}}</button>
    </x-slot:buttons>
</x-modal>
