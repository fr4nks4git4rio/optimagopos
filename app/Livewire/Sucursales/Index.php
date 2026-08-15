<?php

namespace App\Livewire\Sucursales;

use App\Models\Sucursal;
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
    public $page;
    public $perPage;
    public $perPages = [10, 25, 50, 100];
    public $search;
    public $order;
    public $sort;
    public $sorts = [];
    public $filter;
    public $filters = [];

    protected $queryString = ['search', 'perPage', 'page', 'sort', 'order', 'filter'];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        if (user()->is_super_admin)
            $this->sorts = [__('site.branches.index.commercial_name'), __('site.branches.index.rfc'), __('site.branches.index.social_reason'), __('site.branches.index.phone'), __('site.branches.index.client'), __('site.branches.index.subscription')];
        else
            $this->sorts = [__('site.branches.index.commercial_name'), __('site.branches.index.rfc'), __('site.branches.index.social_reason'), __('site.branches.index.phone'), __('site.branches.index.subscription')];
        $this->filters = [__('site.common.actives'), __('site.common.inactives'), __('site.common.all')];
        $this->page = $this->page ?? 1;
        $this->perPage = $this->perPage ?? 10;
        $this->search = $this->search ?? '';
        $this->order = $this->order ?? 'asc';
        $this->sort = $this->sort ?? __('site.branches.index.commercial_name');
        $this->filter = $this->filter ?? __('site.common.actives');
        $this->clientes = [];
    }

    public function getClassSortProperty()
    {
        return $this->order == 'asc' ? 'bi bi-sort-up-alt' : 'bi bi-sort-down-alt';
    }

    public function updated($field)
    {
        if (in_array($field, ['filter', 'search', 'perPage', 'order', 'sort'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $clientes_q = DB::table('tb_clientes')->where('es_cliente', 1)->whereNull('deleted_at');
        if (user()->cliente_id) {
            $clientes_q->where('id', user()->cliente_id);
        }
        $clientes = $clientes_q->get()->map(function ($element) {
            return [
                'value' => $element->id,
                'label' => Crypt::decrypt($element->nombre_comercial)
            ];
        })->toArray();

        $records = $this->query();

        // Paginación manual compatible con Livewire 3
        $currentPage = $this->getPage(); // Obtiene la página real de Livewire
        $total = $records->count();
        $currentItems = $records->forPage($currentPage, $this->perPage)->values();

        $sucursales = new LengthAwarePaginator($currentItems, $total, $this->perPage, $currentPage, ['path' => LengthAwarePaginator::resolveCurrentPath()]);
        return view('livewire.sucursales.index', [
            'sucursales' => $sucursales,
            'clientesAll' => $clientes
        ]);
    }

    public function init()
    {
        if (user()->cannot('viewAny', [Sucursal::class])) {
            $this->dispatch('show-toast', 'No tiene permisos para acceder los registros.', 'danger');
            return redirect()->to('/');
        }
    }

    public function query()
    {
        $query = DB::table('tb_sucursales as s')
            ->select(
                's.id',
                's.logo',
                's.nombre_comercial',
                's.rfc',
                's.razon_social',
                's.telefono',
                's.cliente_id',
                's.deleted_at',
                'c.nombre_comercial as cliente',
                DB::raw("CONCAT(paquete.nombre, ' ( ', subs.estado, ' )') as suscripcion"),
                's.deleted_at',
            )
            ->leftJoin('tb_clientes as c', 'c.id', '=', 's.cliente_id')
            ->leftJoin('tb_suscripciones as subs', 'subs.id', '=', 's.suscripcion_id')
            ->leftJoin('tb_paquetes as paquete', 'paquete.id', '=', 'subs.paquete_id');

        switch ($this->filter) {
            case __('site.common.actives'):
                $query->where('s.deleted_at', null);
                break;
            case __('site.common.inactives'):
                $query->where('s.deleted_at', '!=', null);
                break;
            default:
                $query->where('s.id', '>', 0);
        }

        if (user()->cliente_id) {
            $query->where('s.cliente_id', user()->cliente_id);
        } else {
            if (count($this->clientes) > 0) {
                $query->whereIn('s.cliente_id', $this->clientes);
            }
        }

        $sucursales = $query->get()->map(function ($element) {
            return (array) $element;
        })->toArray();
        $records_final = collect();

        foreach ($sucursales as $sucursal) {
            $sucursal['cliente'] = $sucursal['cliente'] ? Crypt::decrypt($sucursal['cliente']) : '';
            $sucursal = Sucursal::decryptInfo($sucursal);

            if (
                !$this->search
                || Str::contains(Str::upper($sucursal['nombre_comercial']), Str::upper($this->search))
                || Str::contains(Str::upper($sucursal['rfc']), Str::upper($this->search))
                || Str::contains(Str::upper($sucursal['razon_social']), Str::upper($this->search))
                || Str::contains(Str::upper($sucursal['telefono']), Str::upper($this->search))
                || Str::contains(Str::upper($sucursal['cliente']), Str::upper($this->search))
                || Str::contains(Str::upper($sucursal['suscripcion']), Str::upper($this->search))
            ) {
                $records_final->push($sucursal);
            }
        }

        switch ($this->sort) {
            case __('site.branches.index.commercial_name'):
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('nombre_comercial', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('nombre_comercial', SORT_NATURAL)->values();
                break;
            case __('site.branches.index.rfc'):
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('rfc', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('rfc', SORT_NATURAL)->values();
                break;
            case __('site.branches.index.social_reason'):
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('razon_social', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('razon_social', SORT_NATURAL)->values();
                break;
            case __('site.branches.index.phone'):
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('telefono', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('telefono', SORT_NATURAL)->values();
                break;
            case __('site.branches.index.client'):
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('cliente', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('cliente', SORT_NATURAL)->values();
                break;
            case __('site.branches.index.subscription'):
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('suscripcion', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('suscripcion', SORT_NATURAL)->values();
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
