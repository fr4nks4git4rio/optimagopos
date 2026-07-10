<x-modal form-action="save">
    <x-slot:title>
        {{ __('site.terminals.save.edit_terminal') }}
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init">

            <x-input label="{{ __('site.terminals.save.name') }}" type="text" model="nombre" />

            <x-input label="__('site.terminals.save.identifier')" disabled type="text" model="identificador" />

            <div class="mb-1">
                <label for="">{{ __('site.terminals.save.comments') }}:</label>
                <textarea class="form-control" wire:model.defer="comentarios" rows="3"></textarea>
            </div>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">
            {{ __('site.common.close') }}
        </button>
        <button type="submit" class="btn btn-primary">{{ __('site.terminals.save.save_terminal') }}</button>
    </x-slot:buttons>
</x-modal>
