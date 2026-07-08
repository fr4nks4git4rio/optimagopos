<?php

namespace App\Http\Livewire\Layouts;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class Sidebar extends Component
{
    protected $listeners = ['$refresh'];

    public $display;
    public $sidebar_with;

    public function mount()
    {
        $this->display = 'd-sm-inline';
        $this->sidebar_with = 'col-md-3 col-xl-2';
    }

    public function render()
    {
        return view('livewire.layouts.sidebar');
    }
    public function getAdminRoutesActiveProperty()
    {
        $prefix = user()->cliente_id ? 'cliente' : 'admin';
        return Request::is("$prefix/usuarios*")
            || Request::is("$prefix/modulos*")
            || Request::is("$prefix/paquetes*")
            || Request::is("$prefix/clientes*")
            || Request::is("$prefix/comensales*")
            || Request::is("$prefix/sucursales*")
            || Request::is("$prefix/terminales*")
            || Request::is("$prefix/suscripciones*")
            || Request::is("$prefix/configuraciones*")
            || Request::is("$prefix/cuarentena*")
            || Request::is("$prefix/trazas*");
    }

    public function getFacturacionRoutesActiveProperty()
    {
        $prefix = user()->cliente_id ? 'cliente' : 'admin';
        return Request::is("$prefix/pre-facturas*")
            || Request::is("$prefix/almacen-facturas*")
            || Request::is("$prefix/cabecera-factura*")
            || Request::is("$prefix/pre-facturas*")
            || Request::is("$prefix/complementos*")
            || Request::is("$prefix/notas-credito*")
            || Request::is("$prefix/cuentas-cobrar*");
    }
    public function getReportesRoutesActiveProperty()
    {
        $prefix = user()->cliente_id ? 'cliente' : 'admin';
        return Request::is($prefix . '/reportes/historico-operaciones*')
            || Request::is($prefix . '/reportes/logs*')
            || Request::is($prefix . '/reportes/ingresos*')
            || Request::is($prefix . '/reportes/ventas-periodo*')
            || Request::is($prefix . '/reportes/productos-mas-vendidos*');
    }
}
