<x-modal form-action="delete">
    <x-slot:title>
        Eliminar Factura
    </x-slot:title>

    <x-slot:content>
        <x-alert icon="exclamation-octagon" alert="danger">
            <span>Seguro/a que desea desactivar la Factura?</span><br>
            <p class="text-center fw-bold pb-0 mb-0">Esta acción eliminará definitivamente la Factura.</p>
        </x-alert>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">Cerrar</button>
        <button type="submit" class="btn btn-danger">Eliminar Factura</button>
    </x-slot:buttons>
</x-modal>
