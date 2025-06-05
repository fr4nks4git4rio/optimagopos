<x-modal form-action="restore">
    <x-slot:title>
        Reactivar Terminal
    </x-slot:title>

    <x-slot:content>
        <x-alert icon="exclamation-octagon" alert="danger">
            Seguro/a que desea reactivar la Terminal?
        </x-alert>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">Cerrar</button>
        <button type="submit" class="btn btn-site-primary">Reactivar Terminal</button>
    </x-slot:buttons>
</x-modal>
