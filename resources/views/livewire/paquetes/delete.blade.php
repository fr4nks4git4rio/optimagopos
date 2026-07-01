<x-modal form-action="delete">
    <x-slot:title>
        Desactivar Paquete
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init">
            <x-alert icon="exclamation-octagon" alert="danger">
                Seguro/a que desea desactivar el Paquete?
            </x-alert>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
            wire:click="$emit('closeModal')">Cerrar</button>
        <button type="submit" class="btn btn-danger">Desactivar Paquete</button>
    </x-slot:buttons>
</x-modal>
