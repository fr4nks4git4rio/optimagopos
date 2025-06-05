<x-modal form-action="save">
    <x-slot:title>
        {{$terminal->exists ? 'Editar ' : 'Crear '}}Sucursal
    </x-slot:title>

    <x-slot:content>
        <x-select2-component-modals label="Sucursal" placeholder="Seleccione..."
            class="form-control"
            :options="$sucursales" model="sucursal_id" />

        <x-input label="Identificador" disabled type="text" model="identificador" />

        <div class="mb-1">
            <label for="">Comentarios:</label>
            <textarea class="form-control" wire:model.defer="comentarios" rows="3"></textarea>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">
            Cerrar
        </button>
        <button type="submit" class="btn btn-primary">Guardar Terminal</button>
    </x-slot:buttons>
</x-modal>
