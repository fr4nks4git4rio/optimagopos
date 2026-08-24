<x-modal form-action="save">
    <x-slot:title>
        <span
            class="text-capitalize">{{ $user->exists ? __('site.users.save.edit_user') : __('site.users.save.create_user') }}</span>
    </x-slot:title>

    <x-slot:content>
        <div class="row" wire:init="init()">
            <div class="col-12 col-md-3 text-center mb-2">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h2 class="h4 text-capitalize text-center">{{ __('site.users.save.picture') }}</h2>
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
                        <button type="button" class="btn btn-site-primary mt-2 text-capitalize"
                            onclick="document.getElementById('avatar').click()">{{ __('site.users.save.upload_picture') }}
                        </button>
                        @if ($avatar || $avatar_src)
                            <button type="button" class="btn btn-secondary mt-2 text-capitalize"
                                wire:click="removePhoto()">{{ __('site.users.save.remove_picture') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-9">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <x-select2-component-modals label="{{ __('site.users.save.role') }}"
                                    placeholder="{{ __('site.common.select') }}" class="form-control" :options="$rolesAll"
                                    model="roles" />
                            </div>
                            <div class="col-sm-6">
                                <x-input label="{{ __('site.users.save.email') }}" type="email" model="email" />
                            </div>
                        </div>
                        {{-- <div class="row mb-2">
                            <div class="col-sm-12">
                                <x-select2-multiple-component-modals label="{{ __('site.users.save.subscriptions') }}"
                                    placeholder="{{ __('site.common.select') }}" :options="$suscripcionesAll" class="form-control"
                                    model="suscripciones" :dynamic="true" />
                            </div>
                        </div> --}}
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <x-input label="{{ __('site.users.save.first_name') }}" type="text"
                                    model="nombre" />
                            </div>
                            <div class="col-sm-6">
                                <x-input label="{{ __('site.users.save.last_name') }}" type="text"
                                    model="apellidos" />
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <x-input label="{{ __('site.users.save.password') }}" type="password"
                                    model="password" />
                            </div>
                            <div class="col-sm-6">
                                <x-input label="{{ __('site.users.save.confirm_password') }}" type="password"
                                    model="password_confirmation" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary text-capitalize" data-bs-dismiss="modal"
            wire:click="$dispatch('closeModal')">
            {{ __('site.common.close') }}
        </button>
        <button type="submit" class="btn btn-site-primary text-capitalize">{{ __('site.common.save') }}</button>
    </x-slot:buttons>
</x-modal>
