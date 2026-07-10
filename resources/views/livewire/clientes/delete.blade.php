<x-modal form-action="delete">
    <x-slot:title>
        {{ __('site.clients.delete.delete_client') }}
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init">
            <x-alert icon="exclamation-octagon" alert="danger">
                {{ __('site.clients.delete.are_you_sure') }}
            </x-alert>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
            wire:click="$emit('closeModal')">{{ __('site.common.close') }}</button>
        <button type="submit" class="btn btn-danger text-capitalize">{{ __('site.clients.delete.confirm_delete') }}</button>
    </x-slot:buttons>
</x-modal>
