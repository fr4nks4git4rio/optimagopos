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

    public function moduloActivo($modulo)
    {
        modulo_activo($modulo);
    }
    public function getAdminRoutesActiveProperty()
    {
        return Request::is('usuarios*')
        || Request::is('clientes*')
        || Request::is('comensales*')
        || Request::is('sucursales*')
        || Request::is('terminales*')
        || Request::is('trazas*');
    }

    public function getFacturacionRoutesActiveProperty()
    {
        return Request::is('almacen-facturas*')
        || Request::is('cabecera-factura*');
    }
    public function getReportesRoutesActiveProperty()
    {
        return Request::is('reportes/tickets*');
    }
}
