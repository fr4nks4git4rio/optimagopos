<?php

namespace App\Http\Livewire\Paquetes;

use App\Models\Paquete;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $perPage;
    public $perPages = [10, 25, 50, 100];
    public $search;
    public $order;
    public $sort;
    public $sorts = [];
    public $filter;
    public $filters = [];

    protected $queryString = ['search', 'perPage', 'sort', 'order', 'filter'];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->sorts = [__('site.packages.list.name'), __('site.packages.list.description'), __('site.packages.list.price'), __('site.packages.list.modules')];
        $this->filters = [__('site.common.actives'), __('site.common.inactives'), __('site.common.all')];
        $this->perPage = $this->perPage ?? 10;
        $this->search = $this->search ?? '';
        $this->order = $this->order ?? 'asc';
        $this->sort = $this->sort ?? __('site.packages.list.name');
        $this->filter = $this->filter ?? __('site.common.actives');
    }

    public function getClassSortProperty()
    {
        return $this->order == 'asc' ? 'bi bi-sort-up-alt' : 'bi bi-sort-down-alt';
    }

    public function render()
    {
        $paquetes = $this->query()->groupBy('p.id')->get();
        $total = $paquetes->count();
        $records = $paquetes->forPage($this->page, $this->perPage);
        $paquetes = new LengthAwarePaginator($records, $total, $this->perPage, $this->page);
        return view('livewire.paquetes.index', [
            'paquetes' => $paquetes,
        ]);
    }

    public function init()
    {
        if (user()->cannot('viewAny', [Paquete::class])) {
            $this->emit('show-toast', __('site.common.client_no_permissions'), 'danger');
            return redirect()->to('/');
        }
    }

    public function query()
    {
        $query = match ($this->filter) {
            __('site.common.actives') => DB::table('tb_paquetes as p')->whereNull('p.deleted_at'),
            __('site.common.inactives') => DB::table('tb_paquetes as p')->whereNotNull('p.deleted_at'),
            default => DB::table('tb_paquetes as p'),
        };

        $query->select(
            'p.id',
            'p.nombre',
            'p.descripcion',
            'p.precio',
            DB::raw("GROUP_CONCAT(m.nombre SEPARATOR ', ') as modulos"),
            'p.deleted_at'
        );

        $query->leftJoin('tb_paquetes_modulos as pm', 'p.id', 'pm.paquete_id')
            ->leftJoin('tb_modulos as m', function ($join) {
                $join->on('m.id', 'pm.modulo_id')
                    ->whereNull('m.deleted_at');
            });

        if ($this->search) {
            $query->where(function ($query) {
                $query->orWhere('p.nombre', 'like', "%$this->search%")
                    ->orWhere('p.descripcion', 'like', "%$this->search%")
                    ->orWhere('p.precio', 'like', "%$this->search%")
                    ->orWhere('m.nombre', 'like', "%$this->search%");
            });
        }
        switch ($this->sort) {
            case __('site.packages.list.name'):
                if ($this->order == 'asc')
                    $query->orderBy('p.nombre');
                else
                    $query->orderByDesc('p.nombre');
            case __('site.packages.list.description'):
                if ($this->order == 'asc')
                    $query->orderBy('p.descripcion');
                else
                    $query->orderByDesc('p.descripcion');
            case __('site.packages.list.price'):
                if ($this->order == 'asc')
                    $query->orderBy('p.precio');
                else
                    $query->orderByDesc('p.precio');
            case __('site.packages.list.modules'):
                if ($this->order == 'asc')
                    $query->orderBy('m.nombre');
                else
                    $query->orderByDesc('m.nombre');
        }
        return $query;
    }

    public function changeSort($sort)
    {
        $this->order = !$this->order || $this->sort != $sort ? 'asc' : ($this->order == 'asc' ? 'desc' : '');
        $this->sort = !$this->order ? '' : $sort;
    }
}
