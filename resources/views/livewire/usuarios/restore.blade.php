<x-modal form-action="restore">
    <x-slot:title>
        Activar Usuario
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init">
            <x-alert icon="exclamation-octagon" alert="danger">
                Seguro/a que desea activar el Usuario?
            </x-alert>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
            wire:click="$emit('closeModal')">Cerrar</button>
        <button type="submit" class="btn btn-site-primary">Activar Usuario</button>
    </x-slot:buttons>
</x-modal>
