<x-modal form-action="delete">
    <x-slot:title>
        {{ __('site.invoices.delete.title') }}
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init">
            <x-alert icon="exclamation-octagon" alert="danger">
                {!! __('site.invoices.delete.are_you_sure', ['type' => $this->type]) !!}
            </x-alert>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
            wire:click="$emit('closeModal')">{{ __('site.common.close') }}</button>
        <button type="submit" class="btn btn-danger">{{ __('site.invoices.delete.confirm_delete_invoice') }}</button>
    </x-slot:buttons>
</x-modal>
