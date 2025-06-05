<?php

namespace App\Http\Livewire\Auth;

use App\Http\Livewire\Layouts\Modal;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class UpdateProfile extends Modal
{
    use WithFileUploads;

    public $nombre;
    public $apellidos;

    public $email;
    public $avatar;
    public $avatar_src;
    // public $two_factor_authentication_enabled;

    protected $listeners = ['removePhoto'];

    public function mount()
    {

        $this->nombre = user()->nombre;
        $this->apellidos = user()->apellidos;
        $this->email = user()->email;
        $this->avatar = user()->avatar_uri;
        // $this->two_factor_authentication_enabled = user()->twoFactorAuthenticationEnabled();

        $this->avatar_src = $this->avatar;
    }

    public function render()
    {
        return view('livewire.auth.update-profile');
    }

    public function update()
    {
        $data = $this->validate([
            'nombre' => ['required'],
            'apellidos' => ['required']
        ], [
            'nombre.required' => 'Campo requerido.',
            'apellidos.required' => 'Campo requerido.'
        ]);
        $user = User::find(user()->id);
        $user->fill($data);

        if ($this->avatar && !is_string($this->avatar)) {
            $ext = $this->avatar->extension();
            $nombre = Str::uuid() . ".$ext";

            $this->avatar->storeAs('', $nombre, 'avatars');
            $user->avatar = $nombre;
        } else {
            if (user()->avatar && Storage::disk('avatars')->exists(user()->avatar)) {
                Storage::disk('avatars')->delete(user()->avatar);
            }
            $user->avatar = null;
        }

        $attributes = Arr::except(
            $user->getDirty(),
            ['password', 'created_at', 'updated_at', 'deleted_at']
        );
        activity('Perfil de Usuario Actualizado')
            ->on($user)
            ->event('updated')
            ->withProperty('attributes', $attributes)
            ->withProperty('old', Arr::only($user->getOriginal(), array_keys($attributes)))
            ->log('Perfil de Usuario: ' . $user->email . ' ha sido actualizado.');

        $user->saveQuietly();

        $this->emit('show-toast', 'Perfil actualizado.');

        $this->emit('$refresh');
        $this->emit('closeModal');
    }

    public function removePhoto()
    {
        $this->avatar = null;
    }

    public function getHasAvatarProperty()
    {
        return $this->avatar != '';
    }
}
