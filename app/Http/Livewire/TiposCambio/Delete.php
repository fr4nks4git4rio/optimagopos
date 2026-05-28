<?php

namespace App\Http\Livewire\TiposCambio;

use App\Http\Livewire\Layouts\Modal;
use App\Models\ExchangeRate;
use App\Models\ExpenseType;
use App\Models\TipoCambio;

class Delete extends Modal
{
    public $scope;
    public TipoCambio $tipoCambio;

    public function render()
    {
        return view('livewire.tipos-cambio.delete');
    }

    public function delete()
    {
        $this->tipoCambio->delete();

        if($this->scope)
            $this->emitTo($this->scope, 'tipo-cambio-deleted');

        $this->emit('show-toast', 'Tasa de cambio eliminada.');
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
