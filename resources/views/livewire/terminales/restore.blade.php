<x-modal form-action="restore">
    <x-slot:title>
        {{__('site.terminals.restore.restore_terminal')}}
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init">
            <x-alert icon="exclamation-octagon" alert="danger">
                {{__('site.terminals.restore.are_you_sure')}}
            </x-alert>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
            wire:click="$emit('closeModal')">{{__('site.common.close')}}</button>
        <button type="submit" class="btn btn-site-primary">{{__('site.terminals.restore.confirm_restore')}}</button>
    </x-slot:buttons>
</x-modal>
