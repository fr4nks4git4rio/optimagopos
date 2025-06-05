<?php

namespace App\Http\Livewire\Sucursales;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Sucursal;

class Restore extends Modal
{
    public $sucursal_id;

    public function render()
    {
        return view('livewire.sucursales.restore');
    }

    public function restore()
    {
        $sucursal = Sucursal::onlyTrashed()->find($this->sucursal_id);
        if(!$sucursal){
            $this->emit('show-toast', 'Sucursal no encontrada.', 'danger');
            $this->emit('closeModal');
            return;
        }
        $sucursal->restore();

        $this->emit('show-toast', 'Sucursal reactivada.');
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
