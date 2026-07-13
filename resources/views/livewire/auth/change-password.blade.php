<x-modal form-action="update">
    <x-slot:title>
        {{ __('site.change_password.title') }}
    </x-slot:title>

    <x-slot:content>
        <div class="mb-2">
            <x-input label="{{ __('site.change_password.current_password') }}" type="password" model="current_password" />
        </div>
        <div class="mb-2">
            <x-input label="{{ __('site.change_password.new_password') }}" type="password" model="password" />
        </div>
        <div class="mb-2">
            <x-input label="{{ __('site.change_password.password_confirmation') }}" type="password"
                model="password_confirmation" />
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">
            {{ __('site.common.close') }}
        </button>
        <button type="submit" class="btn btn-site-primary">
            {{ __('site.change_password.change_password') }}
        </button>
    </x-slot:buttons>
</x-modal>
