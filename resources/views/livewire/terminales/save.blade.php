<x-modal form-action="save">
    <x-slot:title>
        {{ $terminal->exists ? 'Editar ' : 'Crear ' }}Terminal
    </x-slot:title>

    <x-slot:content>
        @if (user()->is_super_admin)
            <x-select2-component-modals label="Cliente" placeholder="Seleccione..." class="form-control" :options="$clientes"
                model="cliente_id" :lazy="true" />
        @endif
        <x-select2-component-modals label="Sucursal" placeholder="Seleccione..." class="form-control" :options="$sucursales"
            model="sucursal_id" :dynamic="true" />

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
