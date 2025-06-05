<x-modal form-action="delete">
    <x-slot:title>
        Desactivar Usuario
    </x-slot:title>

    <x-slot:content>
        <x-alert icon="exclamation-octagon" alert="danger">
            Seguro/a que desea desactivar el Usuario?
        </x-alert>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">Cerrar</button>
        <button type="submit" class="btn btn-danger">Desactivar Usuario</button>
    </x-slot:buttons>
</x-modal>
