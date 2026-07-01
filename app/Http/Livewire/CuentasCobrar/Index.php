<?php

namespace App\Http\Livewire\CuentasCobrar;

use App\Http\Libraries\Pdf;
use App\Models\Factura;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Livewire\Component;

class Index extends Component
{
    public $cliente;
    public $fecha_inicio;
    public $fecha_fin;
    public $tipo;
    public $perPage;
    public $perPages;
    public $order;
    public $sort;
    public $sorts;

    public $tipos = ['factura' => 'Factura', 'nota_venta' => 'Nota de Venta'];
    public $clientes = [];
    public $monedas = ['USD', 'MXN'];

    public $iframeContainerClass = '';
    public $iframeSrc = '';


    protected $queryString = [];

    protected $listeners = ['$refresh', 'perPage', 'order', 'sort', 'tipo'];

    public function mount()
    {
        $this->sorts = ['F. Int.', 'Fecha Factura', 'Receptor', 'Tipo', 'Moneda', 'Total', 'Pendiente'];
        $this->perPages = [10, 25, 50, 100];

        $this->clientes = DB::table('tb_clientes')
            ->select('id', 'nombre_comercial')
            ->orderBy('nombre_comercial')
            ->whereNull('deleted_at')
            ->whereNotNull('nombre_comercial')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => Crypt::decrypt($item->nombre_comercial),
                    'label' => Crypt::decrypt($item->nombre_comercial),
                ];
            })
            ->toArray();
    }

    public function render()
    {
        return view('livewire.cuentas-cobrar.index');
    }

    public function showPdf($id)
    {
        $name = Factura::generatePdf($id, true);
        $this->iframeSrc = Request::root() . "/$name?" . time();
        $this->iframeContainerClass = 'show';
    }
}
