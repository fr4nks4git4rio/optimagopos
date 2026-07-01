<?php

namespace App\Http\Livewire\Paquetes;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Paquete;

class Delete extends Modal
{
    public Paquete $paquete;

    public function render()
    {
        return view('livewire.paquetes.delete');
    }

    public function init()
    {
        if (user()->cannot('delete', $this->paquete)) {
            $this->emit('show-toast', 'No tiene permisos para realizar estar acción.', 'danger');
            $this->emit('closeModal');
            return;
        }
    }

    public function delete()
    {
        $this->paquete->delete();

        $this->emit('show-toast', 'Paquete desactivado.');
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
