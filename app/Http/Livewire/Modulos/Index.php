<?php

namespace App\Http\Livewire\Modulos;

use App\Models\Modulo;
use Illuminate\Pagination\LengthAwarePaginator;
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
        $this->sorts = [__('site.modules.list.name'), __('site.modules.list.description'), __('site.modules.list.functions_count'), __('site.modules.list.base_cost')];
        $this->filters = [__('site.common.actives'), __('site.common.inactives'), __('site.common.all')];

        $this->perPage = $this->perPage ?? 10;
        $this->search = $this->search ?? '';
        $this->order = $this->order ?? 'asc';
        $this->sort = $this->sort ?? __('site.modules.list.name');
        $this->filter = $this->filter ?? __('site.common.actives');
    }

    public function getClassSortProperty()
    {
        return $this->order == 'asc' ? 'bi bi-sort-up-alt' : 'bi bi-sort-down-alt';
    }

    public function render()
    {
        // $modulos = $this->query();
        // $total = $modulos->count();
        // $records = $modulos->forPage($this->page, $this->perPage);
        // $modulos = new LengthAwarePaginator($records, $total, $this->perPage, $this->page);
        return view('livewire.modulos.index', [
            'modulos' => $this->query()->paginate($this->perPage),
        ]);
    }

    public function init()
    {
        if (user()->cannot('viewAny', [Modulo::class])) {
            $this->emit('show-toast', __('site.common.client_no_permissions'), 'danger');
            return redirect()->to('/');
        }
    }

    public function query()
    {
        $query = match ($this->filter) {
            __('site.common.actives') => Modulo::withoutTrashed(),
            __('site.common.inactives') => Modulo::onlyTrashed(),
            default => Modulo::withTrashed(),
        };

        if ($this->search) {
            $query->where(function ($query) {
                $query->orWhere('nombre', 'like', "%$this->search%")
                    ->orWhere('descripcion', 'like', "%$this->search%")
                    ->orWhere('cant_funciones', 'like', "%$this->search%")
                    ->orWhere('costo_base', 'like', "%$this->search%");
            });
        }
        switch ($this->sort) {
            case __('site.modules.list.name'):
                if ($this->order == 'asc')
                    $query->orderBy('nombre');
                else
                    $query->orderByDesc('nombre');
            case __('site.modules.list.description'):
                if ($this->order == 'asc')
                    $query->orderBy('descripcion');
                else
                    $query->orderByDesc('descripcion');
            case __('site.modules.list.cant_functions'):
                if ($this->order == 'asc')
                    $query->orderBy('cant_funciones');
                else
                    $query->orderByDesc('cant_funciones');
            case __('site.modules.list.base_cost'):
                if ($this->order == 'asc')
                    $query->orderBy('costo_base');
                else
                    $query->orderByDesc('costo_base');
        }
        return $query;
    }

    public function changeSort($sort)
    {
        $this->order = !$this->order || $this->sort != $sort ? 'asc' : ($this->order == 'asc' ? 'desc' : '');
        $this->sort = !$this->order ? '' : $sort;
    }
}
