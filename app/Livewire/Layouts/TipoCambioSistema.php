<?php

namespace App\Livewire\Layouts;

use App\Models\TipoCambioSistema as ModelsTipoCambioSistema;
use Livewire\Component;

class TipoCambioSistema extends Component
{
    public $tipo_cambio;
    public $loading = false;

    protected $listeners = ['saveTipoCambio'];

    public function render()
    {
        $this->tipo_cambio = get_tipo_cambio_sistema()->tasa;
        return view('livewire.layouts.tipo-cambio-sistema');
    }

    public function saveTipoCambio()
    {
        try {
        ModelsTipoCambioSistema::CreateOrUpdate($this->tipo_cambio);
        $this->dispatch('show-toast', 'Tipo de Cambio guardado satisfactoriamente!');
        } catch (\InvalidArgumentException $e) {
            $this->dispatch('show-toast', $e->getMessage(), 'danger');
        }
    }

    public function searchDof()
    {
        $res = ModelsTipoCambioSistema::obtenerTipoCambioUrl();
        if (is_string($res)){
            $this->dispatch('show-toast', $res, 'danger');
        }else {
            $this->dispatch('show-toast', 'Tipo de Cambio guardado satisfactoriamente!');
        }
    }

    public function getHayTipoCambioProperty()
    {
        return $this->tipo_cambio != null;
    }
}
