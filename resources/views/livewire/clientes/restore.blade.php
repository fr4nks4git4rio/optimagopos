<x-modal form-action="restore">
    <x-slot:title>
        Reactivar Cliente
    </x-slot:title>

    <x-slot:content>
        <x-alert icon="exclamation-octagon" alert="danger">
            Seguro/a que desea reactivar el Cliente?
        </x-alert>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">Cerrar</button>
        <button type="submit" class="btn btn-site-primary">Reactivar Cliente</button>
    </x-slot:buttons>
</x-modal>
