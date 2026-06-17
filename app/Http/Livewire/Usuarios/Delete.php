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
        return view('livewire.administracion.usuarios.delete');
    }

    public function init(){
        if (user()->cannot('delete', $this->usuario)) {
            $this->emit('show-toast', 'No tiene permisos para realizar estar acción.', 'danger');
            $this->emit('closeModal');
            return;
        }
    }

    public function delete()
    {
        $this->usuario->delete();

        $this->emit('show-toast', 'Usuario desactivado.');
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
