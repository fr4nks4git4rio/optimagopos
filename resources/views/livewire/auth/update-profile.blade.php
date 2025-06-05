<x-modal form-action="update">
    <x-slot:title>
        Modificar Perfil
    </x-slot:title>
    <x-slot:content>
        <div class="row">
            <div x-data="{avatar_uploaded:false}" class="col-sm-3 text-center"
                 x-on:livewire-upload-finish="avatar_uploaded=true;$wire.avatar_src = URL.createObjectURL(document.getElementById('avatar').files[0])">
                <label for="">Foto</label>
                @if(!$this->has_avatar)
                    <img src="{{asset('img/avatars/no_avatar.png')}}" alt="" class="img-thumbnail rounded-4"
                         id="avatar_image">
                @else
                    <template x-if="avatar_uploaded">
                        <img src="{{$avatar_src}}" alt="" class="img-thumbnail rounded-4">
                    </template>
                    <template x-if="!avatar_uploaded">
                        <img src="{{asset($avatar_src)}}" alt="" class="img-thumbnail rounded-4">
                    </template>
                @endif

                <input type="file" style="display: none" id="avatar" wire:model="avatar"
                       accept=".jpg,.jpeg,.png">
                <button type="button" class="btn btn-site-primary mt-2"
                        onclick="document.getElementById('avatar').click()">Subir Avatar
                </button>
                @if($this->has_avatar)
                    <button type="button" class="btn btn-secondary mt-2"
                            wire:click="$emit('removePhoto')">Quitar Avatar
                    </button>
                @endif
            </div>
            <div class="col-sm-9">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <x-input label="Correo" type="email" model="email"/>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <x-input label="Nombre" type="text" model="nombre"/>
                    </div>
                    <div class="col-sm-6">
                        <x-input label="Apellidos" type="text" model="apellidos"/>
                    </div>
                </div>
            </div>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">
            Cerrar
        </button>
        <button type="submit" class="btn btn-site-primary">Guardar Perfil</button>
    </x-slot:buttons>
</x-modal>
