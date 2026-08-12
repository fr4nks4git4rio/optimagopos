<?php

namespace App\Livewire\Usuarios;

use App\Livewire\Layouts\Modal;
use App\Models\User;
use Livewire\Component;

class Restore extends Modal
{
    public $usuario;

    public function render()
    {
        return view('livewire.usuarios.restore');
    }

    public function init()
    {
        if (user()->cannot('restore', User::withTrashed()->find($this->usuario))) {
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->dispatch('closeModal');
            return;
        }
    }

    public function restore()
    {
        $this->usuario = User::withTrashed()->find($this->usuario);
        $this->usuario->restore();

        activity(__('site.users.restore.log_restored'))
            ->on($this->usuario)
            ->event('restored')
            ->withProperties(User::parseData($this->usuario->getAttributes()))
            ->log(__('site.users.restore.log_restored_detail',  ['email' => $this->usuario->email]));

        $this->dispatch('show-toast', __('site.users.restore.user_activated'));
        $this->dispatch('$refresh');
        $this->dispatch('closeModal');
    }
}
