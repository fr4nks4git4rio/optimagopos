<?php

namespace App\Http\Livewire\Terminales;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Sucursal;
use App\Models\Terminal;

class Restore extends Modal
{
    public $terminal;

    public function render()
    {
        return view('livewire.terminales.restore');
    }

    public function init()
    {
        if (user()->cannot('restore', Terminal::withTrashed()->find($this->terminal))) {
            $this->emit('show-toast', 'No tiene permisos para realizar estar acción.', 'danger');
            $this->emit('closeModal');
            return;
        }
    }

    public function restore()
    {
        $this->terminal = Terminal::onlyTrashed()->find($this->terminal);
        $this->terminal->restore();

        $this->emit('show-toast', 'Terminal reactivada.');
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
