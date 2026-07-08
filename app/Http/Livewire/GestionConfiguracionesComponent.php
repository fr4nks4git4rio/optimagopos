<?php

namespace App\Http\Livewire;

use App\Models\Config;
use App\Models\Moneda;
use Livewire\Component;
use App\Models\Setting;
use App\Models\Suscripcion;

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
        $oldValue = system_config($field);
        switch ($field) {
            case 'precio_sucursal_adicional':
                if ($value < $oldValue) {
                    Suscripcion::with('paquete')->lazy()->each(function (Suscripcion $suscripcion) use ($value, $oldValue) {
                        if ($suscripcion->cant_sucursales > $suscripcion->paquete->cant_sucursales) {

                            $suc_extras = $suscripcion->cant_sucursales - $suscripcion->paquete->cant_sucursales;
                            $precio_extra = $suscripcion->precio_extra - ($suc_extras * $oldValue);
                            $precio_extra += $suc_extras * $value;

                            $suscripcion->precio_extra = $precio_extra;
                            $suscripcion->precio_total = $suscripcion->precio_paquete + $suscripcion->precio_extra;
                            $suscripcion->total = $suscripcion->precio_total - $suscripcion->descuento;
                            $suscripcion->save();
                        }
                    });
                } elseif ($value > $oldValue) {
                    Suscripcion::whereHas('cliente', function ($query) {
                        $query->where('es_cliente_fiel', 0);
                    })->with('paquete')->lazy()->each(function (Suscripcion $suscripcion) use ($value, $oldValue) {
                        if ($suscripcion->cant_sucursales > $suscripcion->paquete->cant_sucursales) {

                            $suc_extras = $suscripcion->cant_sucursales - $suscripcion->paquete->cant_sucursales;
                            $precio_extra = $suscripcion->precio_extra - ($suc_extras * $oldValue);
                            $precio_extra += $suc_extras * $value;

                            $suscripcion->precio_extra = $precio_extra;
                            $suscripcion->precio_total = $suscripcion->precio_paquete + $suscripcion->precio_extra;
                            $suscripcion->total = $suscripcion->precio_total - $suscripcion->descuento;
                            $suscripcion->save();
                        }
                    });
                }
                break;
            case 'precio_terminal_adicional':
                if ($value < $oldValue) {
                    Suscripcion::with('paquete')->lazy()->each(function (Suscripcion $suscripcion) use ($value, $oldValue) {
                        if ($suscripcion->cant_terminales > $suscripcion->paquete->cant_terminales) {

                            $suc_extras = $suscripcion->cant_terminales - $suscripcion->paquete->cant_terminales;
                            $precio_extra = $suscripcion->precio_extra - ($suc_extras * $oldValue);
                            $precio_extra += $suc_extras * $value;

                            $suscripcion->precio_extra = $precio_extra;
                            $suscripcion->precio_total = $suscripcion->precio_paquete + $suscripcion->precio_extra;
                            $suscripcion->total = $suscripcion->precio_total - $suscripcion->descuento;
                            $suscripcion->save();
                        }
                    });
                } elseif ($value > $oldValue) {
                    Suscripcion::whereHas('cliente', function ($query) {
                        $query->where('es_cliente_fiel', 0);
                    })->with('paquete')->lazy()->each(function (Suscripcion $suscripcion) use ($value, $oldValue) {
                        if ($suscripcion->cant_terminales > $suscripcion->paquete->cant_terminales) {

                            $suc_extras = $suscripcion->cant_terminales - $suscripcion->paquete->cant_terminales;
                            $precio_extra = $suscripcion->precio_extra - ($suc_extras * $oldValue);
                            $precio_extra += $suc_extras * $value;

                            $suscripcion->precio_extra = $precio_extra;
                            $suscripcion->precio_total = $suscripcion->precio_paquete + $suscripcion->precio_extra;
                            $suscripcion->total = $suscripcion->precio_total - $suscripcion->descuento;
                            $suscripcion->save();
                        }
                    });
                }
                break;
            case 'precio_usuario_adicional':
                if ($value < $oldValue) {
                    Suscripcion::with('paquete')->lazy()->each(function (Suscripcion $suscripcion) use ($value, $oldValue) {
                        if ($suscripcion->cant_usuarios > $suscripcion->paquete->cant_usuarios) {

                            $suc_extras = $suscripcion->cant_usuarios - $suscripcion->paquete->cant_usuarios;
                            $precio_extra = $suscripcion->precio_extra - ($suc_extras * $oldValue);
                            $precio_extra += $suc_extras * $value;

                            $suscripcion->precio_extra = $precio_extra;
                            $suscripcion->precio_total = $suscripcion->precio_paquete + $suscripcion->precio_extra;
                            $suscripcion->total = $suscripcion->precio_total - $suscripcion->descuento;
                            $suscripcion->save();
                        }
                    });
                } elseif ($value > $oldValue) {
                    Suscripcion::whereHas('cliente', function ($query) {
                        $query->where('es_cliente_fiel', 0);
                    })->with('paquete')->lazy()->each(function (Suscripcion $suscripcion) use ($value, $oldValue) {
                        if ($suscripcion->cant_usuarios > $suscripcion->paquete->cant_usuarios) {

                            $suc_extras = $suscripcion->cant_usuarios - $suscripcion->paquete->cant_usuarios;
                            $precio_extra = $suscripcion->precio_extra - ($suc_extras * $oldValue);
                            $precio_extra += $suc_extras * $value;

                            $suscripcion->precio_extra = $precio_extra;
                            $suscripcion->precio_total = $suscripcion->precio_paquete + $suscripcion->precio_extra;
                            $suscripcion->total = $suscripcion->precio_total - $suscripcion->descuento;
                            $suscripcion->save();
                        }
                    });
                }
                break;
        }

        system_config($field, $value);

        $this->emit('show-toast', 'Configuración global guardada correctamente', 'success');
    }

    public function render()
    {
        return view('livewire.gestion-configuraciones-component');
    }
}
