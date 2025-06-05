<x-modal form-action="verifyCode">
    <x-slot:title>
        Verificación de Doble Factor
    </x-slot:title>

    <x-slot:content>
        <div wire:init="sendCode">
            <div class="alert alert-info">
                Le hemos enviado un código de verificación a su correo. Por favor introduzca el código de verificación.
            </div>
            <x-input label="Código de Verificación" type="text" model="code"/>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" wire:click="sendCode(true)" wire:loading.attr="disabled">Reenviar Código</button>
        <button type="submit" class="btn btn-primary">Verificar Código</button>
    </x-slot:buttons>
</x-modal>
