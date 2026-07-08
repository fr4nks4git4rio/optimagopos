<x-modal form-action="restore">
    <x-slot:title>
        <span class="text-capitalize">{{ __('site.modules.restore.restore_module') }}</span>
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init">
            <x-alert icon="exclamation-octagon" alert="danger">
                {{ __('site.modules.restore.are_you_sure') }}
            </x-alert>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary text-capitalize" data-bs-dismiss="modal"
            wire:click="$emit('closeModal')">{{ __('site.common.close') }}</button>
        <button type="submit" class="btn btn-primary text-capitalize">{{ __('site.modules.restore.confirm_restore') }}</button>
    </x-slot:buttons>
</x-modal>
