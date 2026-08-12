<?php

namespace App\Livewire\TiposCambio;

use App\Livewire\Layouts\Modal;
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
            $this->dispatch('tipo-cambio-deleted')->to($this->scope);

        $this->dispatch('show-toast', 'Tasa de cambio eliminada.');
        $this->dispatch('$refresh');
        $this->dispatch('closeModal');
    }
}
