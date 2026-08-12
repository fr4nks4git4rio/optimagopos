<div class="modal fade" id="{{ $modal_id }}" tabindex="-1" wire:ignore.self>
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('site.invoices.invoice_consecutive.title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="$set('show', '')"></button>
            </div>
            <div class="modal-body">
                <x-input type="text" model="consecutivo"
                    label="{{ __('site.invoices.invoice_consecutive.consecutive') }}" />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('site.common.close') }}
                </button>
                <button type="button" wire:click="guardar()"
                    class="btn btn-success">{{ __('site.invoices.invoice_consecutive.save') }}</button>
            </div>
        </div>
    </div>
</div>
