<?php

namespace App\Http\Livewire\Reportes;

use App\Exports\FacturaEmitidaExport;
use App\Http\Libraries\Pdf;
use App\Models\Facturador;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class ProductosMasVendidos extends Component
{
    use WithPagination;
    public $fechaInicio;
    public $fechaFin;
    public $sucursal;

    public $sucursales = [];
    public $iframeContainerClass = '';
    public $iframeSrc = '';
    //    public $filter = 'Activos';
    //    public $filters;

    protected $queryString = [
        'fechaInicio' => ['except' => null],
        'fechaFin' => ['except' => null],
        'sucursal' => ['except' => null]
    ];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->fechaInicio = $this->fechaInicio ?? today()->format('Y-m-d');
        $this->fechaFin = $this->fechaFin ?? today()->format('Y-m-d');
        $this->sucursal = $this->sucursal ?? null;
    }

    public function hydrate()
    {
        $this->dispatchBrowserEvent('reApplySelect2');
    }
    public function updated()
    {
        $this->dispatchBrowserEvent('reApplySelect2');
    }

    public function render()
    {
        return view('livewire.reportes.productos-mas-vendidos', [
            'records' => $this->query()->get(),
        ]);
    }

    public function init()
    {
        $this->sucursales = DB::table('tb_sucursales')
            ->select('id', 'nombre_comercial', 'razon_social')
            ->whereNull('deleted_at')
            ->where('cliente_id', user()->cliente_id)
            ->get()
            ->map(function ($value, $key) {
                $value->nombre_comercial = Crypt::decrypt($value->nombre_comercial);
                $value->razon_social = Crypt::decrypt($value->razon_social);
                return [
                    'value' => $value->id,
                    'label' => "$value->nombre_comercial | $value->razon_social"
                ];
            })->toArray();
    }

    public function query()
    {
        $query = DB::table('tb_ticket_productos as tp')
            ->join('tb_productos as p', 'tp.producto_id', '=', 'p.id')
            ->leftJoin('tb_tickets as t', 'tp.ticket_id', '=', 't.id')
            ->select(
                'p.id',
                'p.nombre',
                DB::raw('SUM(tp.cantidad) as total_vendido'),
                DB::raw("DATE(t.fecha_transaccion) as fecha_transaccion")
            )
            ->groupBy('p.id', 'p.nombre')
            ->orderByDesc('total_vendido')
            ->limit(10);

        if ($this->fechaInicio) {
            $query->whereDate('t.fecha_transaccion', '>=', $this->fechaInicio);
        }
        if ($this->fechaFin) {
            $query->whereDate('t.fecha_transaccion', '<=', $this->fechaFin);
        }
        if ($this->sucursal) {
            $query->where('t.sucursal_id', $this->sucursal);
        }

        return $query;
    }
}
