<?php

namespace App\Http\Livewire;

use App\Models\Config;
use App\Models\Moneda;
use Livewire\Component;
use App\Models\Setting;

class GestionConfiguracionesComponent extends Component
{
    // Propiedades vinculadas al formulario
    public $cfdi_timbrado_productivo;
    public $iva;
    public $precio_sucursal_adicional;
    public $precio_terminal_adicional;
    public $precio_usuario_adicional;
    public $moneda_sistema;

    public $monedas = [];

    protected $rules = [
        'cfdi_timbrado_productivo' => 'required|in:0,1',
        'iva' => 'required|numeric|min:0|max:100',
        'precio_sucursal_adicional' => 'required|numeric|min:0',
        'precio_terminal_adicional' => 'required|numeric|min:0',
        'precio_usuario_adicional' => 'required|numeric|min:0',
        'moneda_sistema' => 'required|string|size:3',
    ];

    public function mount()
    {
        $this->cfdi_timbrado_productivo = system_config('cfdi_timbrado_productivo');
        $this->iva = system_config('iva');
        $this->precio_sucursal_adicional = system_config('precio_sucursal_adicional');
        $this->precio_terminal_adicional = system_config('precio_terminal_adicional');
        $this->precio_usuario_adicional = system_config('precio_usuario_adicional');
        $this->moneda_sistema = system_config('moneda_sistema');

        $this->monedas = Moneda::all()->pluck('acronimo', 'nombre')->toArray();
    }

    public function updated($field, $value)
    {
        system_config($field, $value);

        $this->emit('show-toast', 'Configuración global guardada correctamente', 'success');
    }

    public function render()
    {
        return view('livewire.gestion-configuraciones-component');
    }
}
