<x-modal form-action="update">
    <x-slot:title>
        Cambiar Contraseña
    </x-slot:title>

    <x-slot:content>
        <div class="mb-2">
            <x-input label="Contraseña Actual" type="password" model="current_password"/>
        </div>
        <div class="mb-2">
            <x-input label="Nueva Contraseña" type="password" model="password"/>
        </div>
        <div class="mb-2">
            <x-input label="Repetir Nueva Contraseña" type="password" model="password_confirmation"/>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">Cerrar</button>
        <button type="submit" class="btn btn-site-primary">Cambiar Contraseña</button>
    </x-slot:buttons>
</x-modal>
