<div class="row justify-content-center align-items-center min-vh-100 py-5 mx-0 bg-light">
    <div class="col-12 col-sm-10 col-md-6 col-lg-4">

        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-white text-dark rounded-circle mb-3 shadow-sm"
                style="width: 56px; height: 56px;">
                <i class="bi bi-shield-lock-fill text-primary" style="font-size: 1.5rem;"></i>
            </div>
            <h2 class="fw-bold text-dark h4 mb-2">{{ __('Reset Password') }}</h2>
            <p class="text-muted small px-2">
                Estás a un paso de asegurar tu cuenta. Elige una nueva contraseña que sea fácil de recordar pero difícil
                de adivinar.
            </p>
        </div>

        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-4 p-sm-5">

                <form wire:submit.prevent="resetPassword">
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label for="email"
                            class="form-label small fw-semibold text-uppercase text-muted tracking-wider">
                            {{ __('Email Address') }}
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i
                                    class="bi bi-envelope"></i></span>
                            <input id="email" type="email"
                                class="form-control bg-light border-start-0 ps-0 @error('email') is-invalid @enderror"
                                name="email" wire:model="email" required autocomplete="email" autofocus
                                placeholder="ejemplo@correo.com">
                        </div>
                        @error('email')
                            <span class="invalid-feedback d-block fw-medium mt-2" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password"
                            class="form-label small fw-semibold text-uppercase text-muted tracking-wider">
                            Nueva Contraseña
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i
                                    class="bi bi-lock"></i></span>
                            <input id="password" type="password"
                                class="form-control bg-light border-start-0 ps-0 @error('password') is-invalid @enderror"
                                name="password" wire:model="password" required autocomplete="new-password"
                                placeholder="••••••••">
                        </div>
                        @error('password')
                            <span class="invalid-feedback d-block fw-medium mt-2" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password-confirm"
                            class="form-label small fw-semibold text-uppercase text-muted tracking-wider">
                            {{ __('Confirm Password') }}
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i
                                    class="bi bi-check-circle"></i></span>
                            <input id="password-confirm" type="password"
                                class="form-control bg-light border-start-0 ps-0" name="password_confirmation"
                                wire:model="password_confirmation" required autocomplete="new-password"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" wire:loading.attr="disabled" wire:target="resetPassword"
                            class="btn btn-site-primary btn-lg w-100 py-2.5 fw-semibold text-uppercase tracking-wide"
                            style="font-size: 0.9rem;">
                            <span wire:loading.remove wire:target="resetPassword" >{{ __('Reset Password') }}</span>
                            <span wire:loading wire:target="resetPassword">
                                <span class="spinner-border spinner-border-sm me-2" role="status"
                                    aria-hidden="true"></span>{{ __('Updating') }}...
                            </span>
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>
