<x-modal form-action="delete">
    <x-slot:title>
        Eliminar Tasa de Cambio
    </x-slot:title>

    <x-slot:content>
        <x-alert icon="exclamation-octagon" alert="danger">
            Seguro/a que desea eliminar la Tasa de Cambio?
        </x-alert>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
            wire:click="$emit('closeModal')">No</button>
        <button type="submit" class="btn btn-danger">Si</button>
    </x-slot:buttons>
</x-modal>
