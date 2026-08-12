<?php

namespace App\Livewire\Usuarios;

use App\Livewire\Layouts\Modal;
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
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->dispatch('closeModal');
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

        $this->dispatch('show-toast', __('site.users.delete.user_deactivated'));
        $this->dispatch('$refresh');
        $this->dispatch('closeModal');
    }
}
