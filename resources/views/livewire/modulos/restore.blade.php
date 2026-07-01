<x-modal form-action="restore">
    <x-slot:title>
        Reactivar Módulo
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init">
            <x-alert icon="exclamation-octagon" alert="danger">
                Seguro/a que desea reactivar el Módulo?
            </x-alert>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
            wire:click="$emit('closeModal')">Cerrar</button>
        <button type="submit" class="btn btn-site-primary">Reactivar Módulo</button>
    </x-slot:buttons>
</x-modal>
