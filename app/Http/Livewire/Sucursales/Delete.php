<?php

namespace App\Http\Livewire\Sucursales;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Sucursal;

class Delete extends Modal
{
    public Sucursal $sucursal;

    public function render()
    {
        return view('livewire.sucursales.delete');
    }

    public function delete()
    {
        $this->sucursal->delete();

        $this->emit('show-toast', 'Sucursal desactivada.');
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
