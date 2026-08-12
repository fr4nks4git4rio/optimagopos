<x-modal form-action="update">
    <x-slot:title>
        {{ __('site.update_profile.title') }}
    </x-slot:title>
    <x-slot:content>
        <div class="row">
            <div class="col-sm-3 text-center">
                <label for="">{{ __('site.update_profile.picture') }}</label>
                <hr>
                @if ($avatar)
                    <img src="{{ $avatar->temporaryUrl() }}" alt="Logo preview" class="img-thumbnail rounded-4">

                    {{-- 2. Mostrar logo existente de la base de datos si existe --}}
                @elseif ($avatar_src)
                    <img src="{{ asset($avatar_src) }}" alt="Avatar actual" class="img-thumbnail rounded-4">

                    {{-- 3. Imagen por defecto si no hay nada --}}
                @else
                    <img src="{{ asset('img/avatars/no_avatar.png') }}" alt="Sin avatar"
                        class="img-thumbnail rounded-4">
                @endif

                <input type="file" style="display: none" id="avatar" wire:model.live="avatar"
                    accept=".jpg,.jpeg,.png">
                <button type="button" class="btn btn-site-primary mt-2"
                    onclick="document.getElementById('avatar').click()">
                    {{ __('site.update_profile.upload_picture') }}
                </button>
                @if ($this->avatar || $this->avatar_src)
                    <button type="button" class="btn btn-secondary mt-2" wire:click="$dispatch('removePhoto')">
                        {{ __('site.update_profile.remove_picture') }}
                    </button>
                @endif
            </div>
            <div class="col-sm-9">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <x-input label="{{ __('site.update_profile.email') }}" type="email" model="email" disabled />
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <div class="mb-1">
                            <label for="">{{ __('site.update_profile.rol') }}:</label>
                            <input type="text" class="form-control" value="{{ $this->rol }}" disabled>
                        </div>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <x-input label="{{ __('site.update_profile.first_name') }}" type="text" model="nombre" />
                    </div>
                    <div class="col-sm-6">
                        <x-input label="{{ __('site.update_profile.last_name') }}" type="text" model="apellidos" />
                    </div>
                </div>
            </div>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$dispatch('closeModal')">
            {{ __('site.common.close') }}
        </button>
        <button type="submit" class="btn btn-site-primary">{{ __('site.update_profile.save_profile') }}</button>
    </x-slot:buttons>
</x-modal>
