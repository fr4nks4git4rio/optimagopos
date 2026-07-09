<x-modal form-action="delete">
    <x-slot:title>
        {{ __('site.diners.delete.delete_diner') }}
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init">
            <x-alert icon="exclamation-octagon" alert="danger">
                {{ __('site.diners.delete.are_you_sure') }}
            </x-alert>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
            wire:click="$emit('closeModal')">{{ __('site.common.close') }}</button>
        <button type="submit" class="btn btn-danger">{{ __('site.diners.delete.delete_diner') }}</button>
    </x-slot:buttons>
</x-modal>
