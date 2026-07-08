<x-modal form-action="delete">
    <x-slot:title>
        <span class="text-capitalize">{{ __('site.modules.delete.delete_module') }}</span>
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init">
            <x-alert icon="exclamation-octagon" alert="danger">
                {{ __('site.modules.delete.are_you_sure') }}
            </x-alert>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary text-capitalize" data-bs-dismiss="modal"
            wire:click="$emit('closeModal')">{{ __('site.common.close') }}</button>
        <button type="submit" class="btn btn-danger text-capitalize">{{ __('site.modules.delete.confirm_delete') }}</button>
    </x-slot:buttons>
</x-modal>
