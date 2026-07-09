<?php

namespace App\Http\Livewire\Comensales;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

class Restore extends Modal
{
    public $comensal_id;

    public function render()
    {
        return view('livewire.comensales.restore');
    }

    public function init(){
        if (user()->cannot('restoreComensal', Cliente::find($this->comensal_id))) {
            $this->emit('show-toast', 'No tiene permisos para realizar estar acción.', 'danger');
            $this->emit('closeModal');
            return;
        }
    }

    public function restore()
    {
        DB::table('tb_clientes_comensales')
            ->where('cliente_id', user()->cliente_id)
            ->where('comensal_id', $this->comensal_id)
            ->update(['activo' => 1]);

        $this->emit('show-toast', 'Cliente reactivado.');
        $this->emit('$refresh');
        $this->emit('closeModal');
    }
}
