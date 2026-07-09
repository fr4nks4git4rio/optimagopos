<div>
    <a class="btn btn-lg btn-outline-danger w-100 py-2.5 fw-medium d-flex align-items-center justify-content-center gap-2"
        style="font-size: 0.9rem;" href="#" wire:click="loginWithGoogle">
        <i class="bi bi-google"></i>
        <span>{{ __('site.login.enter_with_gmail') }}</span>
    </a>
    @error('social')
        <span class="invalid-feedback d-block mt-2" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
