<x-modal form-action="desactivar">
    <x-slot:title>
        {{ __('site.subscriptions.delete.title') }}
    </x-slot:title>

    <x-slot:content>
        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle"
            style="width: 72px; height: 72px;">
            <i class="bi bi-x-circle fs-1 animate-spin"></i>
        </div>

        <div class="card bg-warning-subtle">
            <div class="card-body">
                <h4 class="fw-bold text-dark mb-2">{{ __('site.subscriptions.delete.question') }}</h4>
                <p class="text-muted small px-3">
                    {{ __('site.subscriptions.delete.are_you_sure') }}
                </p>
            </div>
        </div>

        @if ($selectedSubscriptionData)
            <div class="card bg-light border-0 rounded-3 p-3 text-start mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase tracking-wider fw-semibold"
                        style="font-size: 0.7rem;">{{ __('site.subscriptions.delete.subscription') }}</span>
                    <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.75rem;">
                        #{{ $selectedSubscriptionData['id'] }}
                    </span>
                </div>

                <div class="fw-bold text-dark mb-1">{{ $selectedSubscriptionData['cliente_nombre'] }}</div>
                <div class="text-secondary small mb-3"><i class="bi bi-box-seam me-1"></i>
                    {{ $selectedSubscriptionData['plan_nombre'] }}</div>
            </div>
        @endif

        <div class="alert alert-warning border-0 p-3 mb-0">
            <label for="" class="form-label">{{ __('site.subscriptions.delete.delete_motive') }}:</label>
            <textarea class="form-control" wire:model="motivo_desactivacion" rows="4"></textarea>
            <small class="text-muted"><i class="bi bi-info-circle"></i> {{ __('site.subscriptions.delete.min_chars') }}</small>
            @error('motivo_desactivacion')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
            wire:click="$emit('closeModal')">{{ __('site.common.close') }}</button>
        <button wire:loading.attr="disabled" type="submit" class="btn btn-danger">
            <span wire:loading.remove wire:target="confirmarDesactivacion">
                <i class="bi bi-x-circle-fill me-1"></i> {{ __('site.subscriptions.delete.delete_subscription') }}
            </span>
            <span wire:loading wire:target="confirmarDesactivacion" class="spinner-border spinner-border-sm"
                role="status" aria-hidden="true"></span>
        </button>
    </x-slot:buttons>
</x-modal>
