<x-modal form-action="restore">
    <x-slot:title>
        {{ __('site.clients.restore.restore_client') }}
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init">
            <x-alert icon="exclamation-octagon" alert="danger">
                {{ __('site.clients.restore.are_you_sure') }}
            </x-alert>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
            wire:click="$emit('closeModal')">{{ __('site.common.close') }}</button>
        <button type="submit" class="btn btn-site-primary text-capitalize">{{ __('site.clients.restore.confirm_restore') }}</button>
    </x-slot:buttons>
</x-modal>
