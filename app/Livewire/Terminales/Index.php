<?php

namespace App\Livewire\Terminales;

use App\Models\Sucursal;
use App\Models\Terminal;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $clientes;
    public $sucursales;
    public $suscripciones;
    public $page;
    public $perPage;
    public $perPages;
    public $search;
    public $order;
    public $sort;
    public $sorts;
    public $filter;
    public $filters;
    public $sucursalesAll = [];
    public $suscripcionesAll = [];

    protected $queryString = ['search', 'order', 'sort', 'filter', 'perPage',  'page'];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        if (user()->hasAnyRole(['Admin', 'Manager'])) {
            $this->sorts = [
                __('site.terminals.index.identifier'),
                __('site.terminals.index.name'),
                __('site.terminals.index.is_vk'),
                __('site.terminals.index.branch'),
                __('site.terminals.index.subscription'),
                __('site.terminals.index.comments')
            ];
        } else {
            $this->sorts = [
                __('site.terminals.index.identifier'),
                __('site.terminals.index.name'),
                __('site.terminals.index.is_vk'),
                __('site.terminals.index.branch'),
                __('site.terminals.index.client'),
                __('site.terminals.index.subscription'),
                __('site.terminals.index.comments')
            ];
        }
        $this->filters = [__('site.common.actives'), __('site.common.inactives'), __('site.common.all')];
        $this->search = $this->search ?? '';
        $this->order = $this->order ?? 'asc';
        $this->sort = $this->sort ?? __('site.terminals.index.identifier');
        $this->filter = $this->filter ?? __('site.common.actives');
        $this->page = $this->page ?? 1;
        $this->perPage = $this->perPage ?? 10;
        $this->perPages = [10, 25, 50, 100];
        $this->clientes = [];
        $this->sucursales = [];
        $this->suscripciones = [];

        if (user()->hasAnyRole(['Admin', 'Manager'])) {
            $this->sucursalesAll = DB::table('tb_sucursales')->where('id', user()->cliente_id)
                ->get()->map(function ($element) {
                    return [
                        'value' => $element->id,
                        'label' => Crypt::decrypt($element->nombre_comercial)
                    ];
                })->toArray();

            $this->suscripcionesAll = DB::table('tb_suscripciones as s')
                ->select('s.*', 'paquete.nombre')
                ->leftJoin('tb_paquetes as paquete', 'paquete.id', 's.paquete_id')
                ->where('s.cliente_id', user()->cliente_id)
                ->groupBy('s.id')
                ->get()->map(function ($element) {
                    return [
                        'value' => $element->id,
                        'label' => "$element->nombre ( $element->estado )"
                    ];
                })->toArray();
        }
    }

    public function getClassSortProperty()
    {
        return $this->order == 'asc' ? 'bi bi-sort-up-alt' : 'bi bi-sort-down-alt';
    }

    public function updated($field)
    {
        if (in_array($field, ['filter', 'search', 'perPage', 'order', 'sort', 'clientes', 'sucursales', 'suscripciones'])) {
            $this->resetPage();
        }

        if (Str::startsWith($field, 'clientes')) {
            $this->sucursalesAll = DB::table('tb_sucursales')
                ->whereNull('deleted_at')
                ->whereIn('cliente_id', $this->clientes)
                ->get()->map(function ($element) {
                    return [
                        'value' => $element->id,
                        'label' => Crypt::decrypt($element->nombre_comercial)
                    ];
                })->toArray();

            $this->suscripcionesAll = DB::table('tb_suscripciones as s')
                ->select('s.id', 's.estado', 'paquete.nombre')
                ->leftJoin('tb_paquetes as paquete', 'paquete.id', 's.paquete_id')
                ->whereIn('s.cliente_id', $this->clientes)
                ->groupBy('s.id')
                ->get()->map(function ($element) {
                    return [
                        'value' => $element->id,
                        'label' => "$element->nombre ( $element->estado )"
                    ];
                })->toArray();
        }
        $this->dispatch('reApplySelect2');
    }

    public function hydrate()
    {
        $this->dispatch('reApplySelect2');
    }

    public function render()
    {
        $clientes_q = DB::table('tb_clientes')->where('es_cliente', 1)->whereNull('deleted_at');
        if (user()->hasAnyRole(['Admin', 'Manager'])) {
            $clientes_q->where('id', user()->cliente_id);
        }
        $clientes = $clientes_q->get()->map(function ($element) {
            return [
                'value' => $element->id,
                'label' => Crypt::decrypt($element->nombre_comercial)
            ];
        })->toArray();

        $records = $this->query();

        $currentPage = $this->getPage();
        $total = $records->count();
        $currentItems = $records->forPage($this->page, $this->perPage)->values();

        $terminales = new LengthAwarePaginator($currentItems, $total, $this->perPage, $currentPage, ['path' => LengthAwarePaginator::resolveCurrentPath()]);
        return view('livewire.terminales.index', [
            'terminales' => $terminales,
            'clientesAll' => $clientes
        ]);
    }

    public function init()
    {
        if (user()->cannot('viewAny', [Terminal::class])) {
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            return redirect()->to('/');
        }
    }

    public function query()
    {
        $query = DB::table('tb_terminales as t')
            ->select(
                't.id',
                't.identificador',
                't.nombre',
                't.es_vk',
                't.comentarios',
                't.sucursal_id',
                't.suscripcion_id',
                't.deleted_at',
                's.nombre_comercial as sucursal',
                'c.nombre_comercial as cliente',
                DB::raw("CONCAT(paquete.nombre, ' ( ', subs.estado, ' )') as suscripcion")
            )
            ->leftJoin('tb_sucursales as s', 's.id', '=', 't.sucursal_id')
            ->leftJoin('tb_clientes as c', 'c.id', '=', 's.cliente_id')
            ->leftJoin('tb_suscripciones as subs', 'subs.id', '=', 't.suscripcion_id')
            ->leftJoin('tb_paquetes as paquete', 'paquete.id', '=', 'subs.paquete_id');

        if (user()->hasAnyRole(['Admin', 'Manager'])) {
            $query->where('s.cliente_id', user()->cliente_id);
        }

        if (count($this->clientes) > 0) {
            $query->whereIn('s.cliente_id', $this->clientes);
        }

        if (count($this->sucursales) > 0) {
            $query->whereIn('t.sucursal_id', $this->sucursales);
        }

        if (count($this->suscripciones) > 0) {
            $query->whereIn('t.suscripcion_id', $this->suscripciones);
        }

        switch ($this->filter) {
            case 'Activos':
                $query->where('t.deleted_at', null);
                break;
            case 'Inactivos':
                $query->where('t.deleted_at', '!=', null);
                break;
            default:
                $query->where('t.id', '>', 0);
                break;
        }

        // dd($query->toRawSql());

        $terminales = $query->get()->map(function ($element) {
            return (array) $element;
        });
        $records_final = collect();

        foreach ($terminales as $terminal) {
            $terminal['sucursal'] = $terminal['sucursal'] ? Str::upper(Crypt::decrypt($terminal['sucursal'])) : '';
            $terminal['cliente'] = $terminal['cliente'] ? Str::upper(Crypt::decrypt($terminal['cliente'])) : '';

            if (
                !$this->search
                || Str::contains(Str::upper($terminal['identificador']), Str::upper($this->search))
                || Str::contains(Str::upper($terminal['nombre']), Str::upper($this->search))
                || Str::contains(Str::upper($terminal['comentarios']), Str::upper($this->search))
                || Str::contains(Str::upper($terminal['sucursal']), Str::upper($this->search))
                || Str::contains(Str::upper($terminal['cliente']), Str::upper($this->search))
                || Str::contains(Str::upper($terminal['suscripcion']), Str::upper($this->search))
            ) {
                $records_final->push($terminal);
            }
        }

        switch ($this->sort) {
            case __('site.terminals.index.identifier'):
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('identificador', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('identificador', SORT_NATURAL)->values();
                break;
            case __('site.terminals.index.name'):
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('nombre', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('nombre', SORT_NATURAL)->values();
                break;
            case __('site.terminals.index.is_vk'):
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('es_vk', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('es_vk', SORT_NATURAL)->values();
                break;
            case __('site.terminals.index.branch'):
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('sucursal', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('sucursal', SORT_NATURAL)->values();
                break;
            case __('site.terminals.index.client'):
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('cliente', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('cliente', SORT_NATURAL)->values();
                break;
            case __('site.terminals.index.subscription'):
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('suscripcion', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('suscripcion', SORT_NATURAL)->values();
                break;
            case __('site.terminals.index.comments'):
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('comentarios', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('comentarios', SORT_NATURAL)->values();
                break;
        }

        return $records_final;
    }

    public function changeSort($sort)
    {
        $this->order = !$this->order || $this->sort != $sort ? 'asc' : ($this->order == 'asc' ? 'desc' : '');
        $this->sort = !$this->order ? '' : $sort;
    }
}
