<x-modal form-action="cancel">
    <div wire:loading.delay.longer>
        <div class="loading">
            <img src="{{ asset('img/loading.gif') }}" />
        </div>
    </div>
    <x-slot:title>
        {{ __('site.common.cancel') }} {{ $this->type }}
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init" class="row">
            <div class="col-sm-12">
                <x-alert icon="exclamation-octagon" alert="danger">
                    {!! $this->text_alert !!} en <strong class="text-uppercase">{{ __('site.common.mode') }}: {{ $this->modo }}</strong>
                </x-alert>
            </div>
            <div class="col-sm-12 mb-3">
                <x-select2-component-modals label="{{ __('site.invoices.cancel.cancellation_motive') }}" class="form-control" :options="$motivos" model="motivo" />
            </div>
            @if ($this->mostrar_sustitutos)
                <div class="col-sm-12">
                    <x-select2-component-modals label="{{ __('site.invoices.cancel.substitute_folio') }}" class="form-control" :options="$facturas"
                        model="factura_sustituta" />
                </div>
            @endif
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">
            {{ __('site.common.close') }}
        </button>
        <button type="submit" class="btn btn-danger">{{ __('site.common.cancel') }} {{ $this->type }}</button>
    </x-slot:buttons>
</x-modal>
