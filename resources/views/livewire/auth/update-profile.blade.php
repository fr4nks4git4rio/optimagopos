<x-modal form-action="update">
    <x-slot:title>
        {{__('site.update_profile.title')}}
    </x-slot:title>
    <x-slot:content>
        <div class="row">
            <div x-data="{ avatar_uploaded: false }" class="col-sm-3 text-center"
                x-on:livewire-upload-finish="avatar_uploaded=true;$wire.avatar_src = URL.createObjectURL(document.getElementById('avatar').files[0])">
                <label for="">{{__('site.update_profile.picture')}}</label>
                @if (!$this->has_avatar)
                    <img src="{{ asset('img/avatars/no_avatar.png') }}" alt="" class="img-thumbnail rounded-4"
                        id="avatar_image">
                @else
                    <template x-if="avatar_uploaded">
                        <img src="{{ $avatar_src }}" alt="" class="img-thumbnail rounded-4">
                    </template>
                    <template x-if="!avatar_uploaded">
                        <img src="{{ asset($avatar_src) }}" alt="" class="img-thumbnail rounded-4">
                    </template>
                @endif

                <input type="file" style="display: none" id="avatar" wire:model="avatar" accept=".jpg,.jpeg,.png">
                <button type="button" class="btn btn-site-primary mt-2"
                    onclick="document.getElementById('avatar').click()">
                    {{__('site.update_profile.upload_picture')}}
                </button>
                @if ($this->has_avatar)
                    <button type="button" class="btn btn-secondary mt-2" wire:click="$emit('removePhoto')">
                        {{__('site.update_profile.remove_picture')}}
                    </button>
                @endif
            </div>
            <div class="col-sm-9">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <x-input label="{{__('site.update_profile.email')}}" type="email" model="email" disabled />
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <div class="mb-1">
                            <label for="">{{__('site.update_profile.rol')}}:</label>
                            <input type="text" class="form-control" value="{{ $this->rol }}" disabled>
                        </div>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <x-input label="{{__('site.update_profile.first_name')}}" type="text" model="nombre" />
                    </div>
                    <div class="col-sm-6">
                        <x-input label="{{__('site.update_profile.last_name')}}" type="text" model="apellidos" />
                    </div>
                </div>
            </div>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">
            {{__('site.common.close')}}
        </button>
        <button type="submit" class="btn btn-site-primary">{{__('site.update_profile.save_profile')}}</button>
    </x-slot:buttons>
</x-modal>
