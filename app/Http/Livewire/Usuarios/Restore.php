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
        $this->usuario = User::onlyTrashed()->find($this->usuario);
        $this->usuario->restore();

        $this->emit('show-toast', __('site.users.restore.user_activated'));
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
