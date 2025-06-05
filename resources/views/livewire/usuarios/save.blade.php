<x-modal form-action="save">
    <x-slot:title>
        {{$user->exists ? 'Editar ' : 'Crear '}}Usuario
    </x-slot:title>

    <x-slot:content>
        <div class="row" wire:init="init()">
            <div x-data="{avatar_uploaded:false}" class="col-12 col-md-3 text-center mb-2"
                x-on:livewire-upload-finish="avatar_uploaded=true;$wire.avatar_src = URL.createObjectURL(document.getElementById('avatar').files[0])">
                <label for="">Foto</label>
                <hr>
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
                    wire:click="removePhoto()">Quitar Avatar
                </button>
                @endif
            </div>
            <div class="col-12 col-md-9">
                @if (user()->is_super_admin)
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <x-select2-ajax-component-modals label="Cliente" placeholder="Seleccione..."
                            class="form-control"
                            url="{{route('clientes.load-clientes')}}"
                            model="cliente_id"
                            :dynamic="true" />
                    </div>
                </div>
                @endif
                <div class="row mb-2">
                    <div class="col-sm-8">
                        <x-input label="Correo" type="email" model="email" />
                    </div>
                    <div class="col-sm-4">
                        <x-select2-component-modals label="Rol" placeholder="Seleccione..."
                            class="form-control"
                            :options="$roles"
                            model="rol_id" />
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <x-input label="Nombre" type="text" model="nombre" />
                    </div>
                    <div class="col-sm-6">
                        <x-input label="Apellidos" type="text" model="apellidos" />
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <x-input label="Contraseña" type="password" model="password" />
                    </div>
                    <div class="col-sm-6">
                        <x-input label="Repetir Contraseña" type="password" model="password_confirmation" />
                    </div>
                </div>
            </div>
        </div>
    </x-slot:content>

    <x-slot:buttons>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="$emit('closeModal')">
            Cerrar
        </button>
        <button type="submit" class="btn btn-site-primary">Guardar Usuario</button>
    </x-slot:buttons>
</x-modal>
