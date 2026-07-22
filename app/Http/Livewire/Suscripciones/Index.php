<?php

namespace App\Http\Livewire\Suscripciones;

use App\Models\Suscripcion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $perPage;
    public $perPages;
    public $search;
    public $order;
    public $sort;
    public $sorts;

    protected $queryString = ['search', 'order', 'sort', 'perPage'];

    protected $listeners = ['$refresh'];

    public $iframeContainerClass = '';
    public $iframeSrc = '';

    public function mount()
    {
        $this->sorts = [__('site.subscriptions.index.client'), __('site.subscriptions.index.package'), __('site.subscriptions.index.operations_start'), __('site.subscriptions.index.payments_start'), __('site.subscriptions.index.periodicity'), __('site.subscriptions.index.capacity_infrastructure'), __('site.subscriptions.index.price'), __('site.subscriptions.index.status')];
        $this->search = $this->search ?? '';
        $this->order = $this->order ?? 'asc';
        $this->sort = $this->sort ?? __('site.subscriptions.index.client');
        $this->perPage = $this->perPage ?? 10;
        $this->perPages = [10, 25, 50, 100];
    }

    public function getClassSortProperty()
    {
        return $this->order == 'asc' ? 'bi bi-sort-up-alt' : 'bi bi-sort-down-alt';
    }

    public function render()
    {
        $subs = $this->query();
        $total = $subs->count();
        $records = $subs->forPage($this->page, $this->perPage);
        $subs = new LengthAwarePaginator($records, $total, $this->perPage, $this->page);
        return view('livewire.suscripciones.index', [
            'suscripciones' => $subs,
        ]);
    }

    public function init()
    {
        if (user()->cannot('viewAny', [Suscripcion::class])) {
            $this->emit('show-toast', __('site.common.client_no_permissions'), 'danger');
            return redirect()->to('/');
        }
    }

    public function query()
    {
        $query = DB::table('tb_suscripciones as sub')
            ->select(
                'sub.id',
                'cliente.nombre_comercial as cliente',
                'paquete.nombre as paquete',
                'sub.fecha_inicio_operaciones',
                'sub.fecha_inicio_pagos',
                DB::raw("DATE_FORMAT(sub.fecha_inicio_operaciones, '%d/%m/%Y') as inicio_operaciones"),
                DB::raw("DATE_FORMAT(sub.fecha_inicio_pagos, '%d/%m/%Y') as inicio_pagos"),
                'sub.periodicidad_pagos',
                DB::raw("CONCAT_WS(', ', CONCAT('Sucursales: ', sub.cant_sucursales), CONCAT('Terminales: ', sub.cant_terminales), CONCAT('Usuarios: ', sub.cant_usuarios)) as capacidad"),
                'sub.total',
                'sub.estado',
                'sub.cliente_id'
            )
            ->leftJoin('tb_clientes as cliente', 'cliente.id', '=', 'sub.cliente_id')
            ->leftJoin('tb_paquetes as paquete', 'paquete.id', '=', 'sub.paquete_id');

        $suscripciones = $query->get()->map(function ($element) {
            return (array) $element;
        });
        $records_final = collect();

        $search = $this->search ? Str::upper($this->search) : '';
        foreach ($suscripciones as $sub) {
            $sub['cliente'] = $sub['cliente'] ? Str::upper(Crypt::decrypt($sub['cliente'])) : '';

            if (
                !$search
                || Str::contains($sub['cliente'], $search)
                || Str::contains(Str::upper($sub['paquete']), $search)
                || Str::contains($sub['inicio_operaciones'], $search)
                || Str::contains($sub['inicio_pagos'], $search)
                || Str::contains(Str::upper($sub['periodicidad_pagos']), $search)
                || Str::contains(Str::upper($sub['capacidad']), $search)
                || Str::contains($sub['total'], $search)
                || Str::contains(Str::upper($sub['estado']), $search)
            ) {
                $records_final->push($sub);
            }
        }

        switch ($this->sort) {
            case 'Cliente':
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('cliente', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('cliente', SORT_NATURAL)->values();
                break;
            case 'Paquete':
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('paquete', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('paquete', SORT_NATURAL)->values();
                break;
            case 'Inicio Operaciones':
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('fecha_inicio_operaciones', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('fecha_inicio_operaciones', SORT_NATURAL)->values();
                break;
            case 'Inicio Pagos':
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('fecha_inicio_pagos', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('fecha_inicio_pagos', SORT_NATURAL)->values();
                break;
            case 'Periodicidad':
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('periodicidad_pagos', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('periodicidad_pagos', SORT_NATURAL)->values();
                break;
            case 'Capacidad e Infraestructura':
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('capacidad', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('capacidad', SORT_NATURAL)->values();
                break;
            case 'Precio':
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('total', SORT_NUMERIC)->values();
                else
                    $records_final = $records_final->sortByDesc('total', SORT_NUMERIC)->values();
                break;
            case 'Estado':
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('estado', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('estado', SORT_NATURAL)->values();
                break;
        }

        return $records_final;
    }

    public function descargarPdf($id)
    {
        $name = Suscripcion::find($id)->generarPdf();

        $this->iframeContainerClass = 'show';
        $this->iframeSrc = Request::root() . "/$name";
    }

    public function changeSort($sort)
    {
        $this->order = !$this->order || $this->sort != $sort ? 'asc' : ($this->order == 'asc' ? 'desc' : '');
        $this->sort = !$this->order ? '' : $sort;
    }
}
