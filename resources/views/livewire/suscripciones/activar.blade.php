<x-modal form-action="activar">
    <x-slot:title>
        {{__('site.subscriptions.activate.title')}}
    </x-slot:title>

    <x-slot:content>
        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle"
            style="width: 72px; height: 72px;">
            <i class="bi bi-check2-circle fs-1 animate-spin"></i>
        </div>

        <div class="card bg-info-subtle">
            <div class="card-body">
                <h4 class="fw-bold text-dark mb-2" id="activateModalTitle">{{ __('site.subscriptions.activate.question') }}</h4>
                <p class="text-muted small px-3">
                    {{ __('site.subscriptions.activate.are_you_sure') }}
                </p>
            </div>
        </div>

        @if ($selectedSubscriptionData)
            <div class="card bg-light border-0 rounded-3 p-3 text-start mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase tracking-wider fw-semibold"
                        style="font-size: 0.7rem;">{{ __('site.subscription.activate.subscription') }}</span>
                    <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.75rem;">
                        #{{ $selectedSubscriptionData['id'] }}
                    </span>
                </div>

                <div class="fw-bold text-dark mb-1">{{ $selectedSubscriptionData['cliente_nombre'] }}</div>
                <div class="text-secondary small mb-3"><i class="bi bi-box-seam me-1"></i>
                    {{ $selectedSubscriptionData['plan_nombre'] }}</div>

                <hr class="my-2 opacity-50 border-secondary">

                <div class="d-flex justify-content-between align-items-center mt-2">
                    <span class="text-muted small">{{__('site.subscriptions.activate.periodic_amount')}}:</span>
                    <span
                        class="fw-bold text-success font-monospace">${{ number_format($selectedSubscriptionData['monto'] * $selectedSubscriptionData['multiplicador'], 2) }}
                        / {{ $selectedSubscriptionData['frecuencia'] }}</span>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-1">
                    <span class="text-muted small">{{ __('site.subscriptions.activate.next_charge') }}:</span>
                    <span class="text-dark fw-medium small font-monospace"><i
                            class="bi bi-calendar-event me-1 text-primary"></i>
                        {{ $selectedSubscriptionData['proximo_pago'] }}</span>
                </div>
            </div>
        @endif

        <div class="alert alert-warning border-0 d-flex align-items-start text-start p-3 mb-0"
            style="font-size: 0.85rem;">
            <i class="bi bi-exclamation-triangle-fill text-warning me-2 mt-0.5 fs-6"></i>
            <div>
                {!! __('site.subscriptions.activate.fiscal_note') !!}
            </div>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
            wire:click="$emit('closeModal')">{{ __('site.common.close') }}</button>
        <button wire:loading.attr="disabled" type="submit" class="btn btn-danger">
            <span wire:loading.remove wire:target="confirmarActivacion">
                <i class="bi bi-check-circle-fill me-1"></i> {{ __('site.subscription.activate.activate_subscription') }}
            </span>
            <span wire:loading wire:target="confirmarActivacion" class="spinner-border spinner-border-sm" role="status"
                aria-hidden="true"></span>
        </button>
    </x-slot:buttons>
</x-modal>
