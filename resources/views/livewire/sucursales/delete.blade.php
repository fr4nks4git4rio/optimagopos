<x-modal form-action="delete">
    <x-slot:title>
        {{ __('site.branches.delete.delete_branch') }}
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init">
            <x-alert icon="exclamation-octagon" alert="danger">
                {{ __('site.branches.delete.are_you_sure') }}
            </x-alert>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
            wire:click="$emit('closeModal')">{{ __('site.common.close') }}</button>
        <button type="submit" class="btn btn-danger">{{ __('site.branches.delete.confirm_delete') }}</button>
    </x-slot:buttons>
</x-modal>
