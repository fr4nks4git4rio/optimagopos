<div class="row justify-content-center align-items-center min-vh-100 py-5 mx-0 bg-light">
    <div class="col-12 col-sm-10 col-md-6 col-lg-4">

        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-white text-primary rounded-circle mb-3 shadow-sm"
                style="width: 56px; height: 56px;">
                <i class="bi bi-key-fill" style="font-size: 1.5rem;"></i>
            </div>
            <h2 class="fw-bold text-dark h4 mb-2">{{ __('Reset Password') }}</h2>
            <p class="text-muted small px-3">
                ¿Olvidaste tu contraseña? No te preocupes. Introduce tu correo y te enviaremos un enlace para
                restaurarla.
            </p>
        </div>

        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-4 p-sm-5">

                @if (session('message'))
                    <div class="alert alert-success border-0 py-2.5 px-3 fw-medium small mb-4 text-center rounded-3"
                        role="alert">
                        {{ session('message') }}
                    </div>
                @endif

                <div class="mb-4">
                    <label for="email" class="form-label small fw-semibold text-uppercase text-muted tracking-wider">
                        {{ __('Email Address') }}
                    </label>

                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i
                                class="bi bi-envelope"></i></span>
                        <input id="email" type="email"
                            class="form-control bg-light border-start-0 ps-0 @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}" wire:model="email" required autocomplete="email"
                            autofocus placeholder="ejemplo@correo.com">
                    </div>

                    @error('email')
                        <span class="invalid-feedback d-block fw-medium mt-2" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <button type="button" wire:click="sendResetLinkEmail"
                        class="btn btn-site-primary btn-lg w-100 py-2.5 fw-semibold text-uppercase tracking-wide"
                        style="font-size: 0.9rem;">
                        {{ __('Send Password Reset Link') }}
                    </button>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('login') }}"
                        class="text-decoration-none small fw-semibold text-secondary d-inline-flex align-items-center gap-2">
                        <i class="bi bi-arrow-left"></i> Volver al inicio de sesión
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
