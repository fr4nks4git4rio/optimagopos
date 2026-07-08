<x-modal form-action="save">
    <x-slot:title>
        Editar Terminal
    </x-slot:title>

    <x-slot:content>
        <div wire:init="init">

            <x-input label="Nombre" type="text" model="nombre" />

            <x-input label="Identificador" disabled type="text" model="identificador" />

            <div class="mb-1">
                <label for="">Comentarios:</label>
                <textarea class="form-control" wire:model.defer="comentarios" rows="3"></textarea>
            </div>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">
            Cerrar
        </button>
        <button type="submit" class="btn btn-primary">Guardar Terminal</button>
    </x-slot:buttons>
</x-modal>
