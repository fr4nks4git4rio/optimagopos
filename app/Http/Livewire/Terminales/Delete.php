<?php

namespace App\Http\Livewire\Terminales;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Terminal;
use Livewire\WithFileUploads;

class Delete extends Modal
{
    use WithFileUploads;

    public Terminal $terminal;

    public function render()
    {
        return view('livewire.terminales.delete');
    }

    public function delete()
    {
        $this->terminal->delete();

        $this->emit('show-toast', 'Terminal desactivada.');
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
