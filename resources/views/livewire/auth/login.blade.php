@section('title', __('Login'))

<div class="row justify-content-center align-items-center min-vh-100 py-5 mx-0 bg-light">
    <div class="col-12 col-sm-10 col-md-6 col-lg-4">

        <div class="text-center mb-4">
            <h2 class="fw-bold text-dark h3 mb-1 text-capitalize">{{ __('site.login.welcome') }}</h2>
            <p class="text-muted small">{{ __('site.login.welcome_detail') }}</p>
        </div>

        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-4 p-sm-5">

                <form wire:submit.prevent="login">

                    <div class="mb-3">
                        <x-input-group :label="__('site.login.email')" icon="bi bi-envelope" type="text" model="email" />
                    </div>

                    <div class="mb-3">
                        <x-input-group :label="__('site.login.password')" icon="bi bi-lock" type="password" model="password" />
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4 small">
                        <div class="form-check-sm">
                            <x-checkbox :label="__('site.login.remember_me')" model="remember" />
                        </div>
                        <a href="{{ route('password.forgot') }}" class="text-decoration-none fw-medium text-primary">
                            {{ __('site.login.forgot_password') }}
                        </a>
                    </div>

                    <div class="mb-4">
                        <button
                            class="btn btn-site-primary btn-lg w-100 py-2.5 fw-semibold text-uppercase tracking-wide text-uppercase"
                            style="font-size: 0.9rem;" type="submit">
                            {{ __('site.login.enter') }}
                        </button>
                    </div>

                    <div class="d-flex align-items-center my-4">
                        <hr class="flex-grow-1 text-muted opacity-25 m-0">
                        <span class="mx-3 text-muted small fw-medium text-uppercase text-nowrap"
                            style="font-size: 0.75rem; letter-spacing: 0.05rem;">{{ __('site.login.or_continue_with') }}</span>
                        <hr class="flex-grow-1 text-muted opacity-25 m-0">
                    </div>

                    <livewire:auth.social-login />
                </form>

            </div>
        </div>

    </div>
</div>
