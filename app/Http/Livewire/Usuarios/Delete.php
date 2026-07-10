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
        $this->usuario->suscripciones()->detach();
        $this->usuario->delete();

        $this->emit('show-toast', __('site.users.delete.user_deactivated'));
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
