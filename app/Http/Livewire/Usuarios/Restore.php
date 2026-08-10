<?php

namespace App\Http\Livewire\Usuarios;

use App\Http\Livewire\Layouts\Modal;
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
            $this->emit('show-toast', __('site.common.client_no_permissions'), 'danger');
            $this->emit('closeModal');
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

        $this->emit('show-toast', __('site.users.restore.user_activated'));
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
