<?php

namespace App\Http\Livewire\Usuarios;

use App\Http\Livewire\Layouts\Modal;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;

class Delete extends Modal
{
    use WithFileUploads;

    public User $usuario;

    public function render()
    {
        return view('livewire.usuarios.delete');
    }

    public function init()
    {
        if (user()->cannot('delete', $this->usuario)) {
            $this->emit('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->emit('closeModal');
            return;
        }
    }

    public function delete()
    {
        $user = $this->usuario;
        $attributes = $user->getAttributes();
        $user->suscripciones()->detach();
        $user->delete();

        activity(__('site.users.delete.log_deleted'))
            ->on($user)
            ->event('deleted')
            ->withProperties(User::parseData($attributes))
            ->log(__('site.users.delete.log_deleted_detail',  ['email' => $attributes['email']]));

        $this->emit('show-toast', __('site.users.delete.user_deactivated'));
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
