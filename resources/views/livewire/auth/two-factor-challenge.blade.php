<div class="row justify-content-center align-items-center min-vh-100 py-5 mx-0 bg-light">
    <div class="col-12 col-sm-10 col-md-6 col-lg-4">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-4 p-sm-5">

                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-dark text-white rounded-circle mb-3" style="width: 56px; height: 56px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-shield-check" viewBox="0 0 16 16">
                            <path d="M5.338 1.59a61 61 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.7 10.7 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .34 0c.074-.23.172-.268.293-.117.241-.113.546-.29.893-.533a10.7 10.7 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.8 11.8 0 0 1-2.517 2.453 7.2 7.2 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.2 7.2 0 0 1-1.048-.625 11.8 11.8 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43 63 63 0 0 1 5.072.56z"/>
                            <path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                        </svg>
                    </div>
                    <h2 class="h4 fw-bold text-dark mb-2">Doble Factor de Autenticación</h2>
                    <p class="text-muted small mb-0 px-2">
                        Se ha enviado un código de verificación de 6 dígitos a tu correo electrónico. Por favor, introdúcelo a continuación.
                    </p>
                </div>

                @if (session()->has('message'))
                    <div class="alert alert-success border-0 py-2.5 px-3 fw-medium small mb-4 text-center rounded-3">
                        {{ session('message') }}
                    </div>
                @endif

                <form wire:submit.prevent="verify">
                    <div class="mb-4">
                        <label for="code" class="form-label small fw-semibold text-uppercase text-muted tracking-wider">Código de Verificación</label>
                        <input wire:model="code" id="code" type="text"
                            class="form-control form-control-lg text-center font-monospace tracking-widest fw-bold @error('code') is-invalid @enderror"
                            placeholder="000000" maxlength="6" required autofocus autocomplete="off"
                            style="font-size: 1.5rem; letter-spacing: 0.3rem;">

                        @error('code')
                            <div class="invalid-feedback text-center fw-medium mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4 form-check form-switch d-flex align-items-center gap-2 ps-0">
                        <input wire:model="rememberDevice" id="rememberDevice" type="checkbox" class="form-check-input ms-0 mt-0" style="cursor: pointer;">
                        <label for="rememberDevice" class="form-check-label small text-secondary" style="cursor: pointer;">
                            Confiar en este dispositivo por 30 días
                        </label>
                    </div>

                    <button type="submit" wire:loading.attr="disabled" wire:target="verify"
                        class="btn btn-dark w-100 py-2.5 fw-semibold mb-3 rounded-3 shadow-sm text-uppercase tracking-wide" style="font-size: 0.85rem;">
                        <span wire:loading.remove wire:target="verify">Verificar y Entrar</span>
                        <span wire:loading wire:target="verify">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Verificando...
                        </span>
                    </button>

                    <hr class="text-muted opacity-25 my-4">

                    <div class="d-flex flex-column gap-3 text-center">
                        <div class="small text-muted">
                            ¿No recibiste el código?
                            <button type="button" wire:click="resendCode" wire:loading.attr="disabled"
                                class="btn btn-link btn-sm fw-semibold text-dark text-decoration-none p-0 ms-1 align-baseline">
                                <span wire:loading.remove wire:target="resendCode">Reenviar código</span>
                                <span wire:loading wire:target="resendCode" class="text-muted">Enviando...</span>
                            </button>
                        </div>

                        <div>
                            <button type="button" wire:click="logout"
                                class="btn btn-link btn-sm text-muted text-decoration-none small">
                                Cancelar / Salir
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
