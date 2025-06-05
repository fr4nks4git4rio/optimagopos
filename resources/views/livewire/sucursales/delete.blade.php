<x-modal form-action="delete">
    <x-slot:title>
        Desactivar Sucursal
    </x-slot:title>

    <x-slot:content>
        <x-alert icon="exclamation-octagon" alert="danger">
            Seguro/a que desea desactivar la Sucursal?
        </x-alert>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">Cerrar</button>
        <button type="submit" class="btn btn-danger">Desactivar Sucursal</button>
    </x-slot:buttons>
</x-modal>
