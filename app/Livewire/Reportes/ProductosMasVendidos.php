<?php

namespace App\Livewire\Reportes;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
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
        $this->dispatch('reApplySelect2');
    }
    public function updated()
    {
        $this->dispatch('reApplySelect2');
    }

    public function render()
    {
        return view('livewire.reportes.productos-mas-vendidos', [
            'records' => $this->query()->get(),
        ]);
    }

    public function init()
    {
        if (user()->cannot('reportsBestSellingProducts-viewAny')) {
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            return redirect()->to('/');
        }

        $this->sucursales = DB::table('tb_sucursales')
            ->select('id', 'nombre_comercial', 'razon_social')
            ->whereIn('id', user()->sucursales->pluck('id')->toArray())
            ->whereNull('deleted_at')
            ->where('cliente_id', user()->cliente_id)
            ->get()
            ->map(function ($value, $key) {
                $nombre_comercial = Crypt::decrypt($value->nombre_comercial);
                return [
                    'value' => $value->id,
                    'label' => $nombre_comercial
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
                DB::raw('ROUND(SUM(tp.cantidad), 2) as total_vendido'),
                DB::raw("DATE(t.fecha_transaccion) as fecha_transaccion")
            )
            ->whereNotNull('tp.producto_id')
            ->where('t.modo_entrenamiento', 0)
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
        }else{
            $query->whereIn('sucursal.id', user()->sucursales->pluck('id')->toArray());
        }

        return $query;
    }
}
