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
        $prefix = user()->is_super_admin ? 'admin' : 'cliente';
        return Request::is("$prefix/usuarios*")
            || Request::is("$prefix/clientes*")
            || Request::is("$prefix/comensales*")
            || Request::is("$prefix/sucursales*")
            || Request::is("$prefix/terminales*")
            || Request::is("$prefix/trazas*");
    }

    public function getFacturacionRoutesActiveProperty()
    {
        return Request::is('cliente/pre-facturas*')
            || Request::is('cliente/almacen-facturas*')
            || Request::is('cliente/cabecera-factura*');
    }
    public function getReportesRoutesActiveProperty()
    {
        $prefix = user()->is_super_admin ? 'admin' : 'cliente';
        return Request::is($prefix . '/reportes/tickets*')
            || Request::is($prefix . '/reportes/logs*')
            || Request::is($prefix . '/reportes/ventas-periodo*')
            || Request::is($prefix . '/reportes/productos-mas-vendidos*');
    }
}
