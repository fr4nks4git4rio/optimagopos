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
    public $sorts = ['Nombre', 'Descripción', 'Cant. Funciones', 'Costo Base'];
    public $filter;
    public $filters = ['Activos', 'Inactivos', 'Todos'];

    protected $queryString = ['search', 'perPage', 'sort', 'order', 'filter'];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->perPage = $this->perPage ?? 10;
        $this->search = $this->search ?? '';
        $this->order = $this->order ?? 'asc';
        $this->sort = $this->sort ?? 'Nombre';
        $this->filter = $this->filter ?? 'Activos';
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
            $this->emit('show-toast', 'No tiene permisos para acceder a estos registros.', 'danger');
            return redirect()->to('/');
        }
    }

    public function query()
    {
        $query = match ($this->filter) {
            'Activos' => Modulo::withoutTrashed(),
            'Inactivos' => Modulo::onlyTrashed(),
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
            case 'Nombre':
                if ($this->order == 'asc')
                    $query->orderBy('nombre');
                else
                    $query->orderByDesc('nombre');
            case 'Descripción':
                if ($this->order == 'asc')
                    $query->orderBy('descripcion');
                else
                    $query->orderByDesc('descripcion');
            case 'Cant. Funciones':
                if ($this->order == 'asc')
                    $query->orderBy('cant_funciones');
                else
                    $query->orderByDesc('cant_funciones');
            case 'Costo Base':
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
