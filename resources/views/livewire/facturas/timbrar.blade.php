<x-modal form-action="timbrar">
    <div wire:loading>
        <div class="loading">
            <div class="spinner-border text-primary my-3" role="status"><span class="visually-hidden">{{ __('site.common.loading') }}...</span></div>
        </div>
    </div>
    <x-slot:title>
        Timbrar {{$this->type}}
    </x-slot:title>

    <x-slot:content>
        <div class="row">
            <div class="col-sm-12">
                <x-alert icon="exclamation-octagon" alert="danger">
                    {!! $this->text_alert !!} en <strong>MODO: {{$this->modo}}</strong>
                </x-alert>
            </div>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$dispatch('closeModal')">
            Cerrar
        </button>
        <button type="submit" class="btn btn-danger">Timbrar {{$this->type}}</button>
    </x-slot:buttons>
</x-modal>
