<?php

namespace App\Http\Livewire\Layouts;

use App\Models\TipoCambio as ModelsTipoCambio;
use Livewire\Component;

class TipoCambio extends Component
{
    public $tipo_cambio;
    public $loading = false;

    protected $listeners = ['saveTipoCambio'];

    public function render()
    {
        $this->tipo_cambio = get_tipo_cambio()->tasa;
        return view('livewire.layouts.tipo-cambio');
    }

    public function saveTipoCambio()
    {
        ModelsTipoCambio::CreateOrUpdate($this->tipo_cambio);
        $this->emit('show-toast', 'Tipo de Cambio guardado satisfactoriamente!');
    }

    public function searchDof()
    {
        $res = ModelsTipoCambio::obtenerTipoCambioUrl();
        if (is_string($res)){
            $this->emit('show-toast', $res, 'danger');
        }else {
            $this->emit('show-toast', 'Tipo de Cambio guardado satisfactoriamente!');
        }
    }

    public function getHayTipoCambioProperty()
    {
        return $this->tipo_cambio != null;
    }
}
