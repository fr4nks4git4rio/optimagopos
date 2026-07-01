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
    public $sorts = ['Nombre', 'Descripción', 'Precio', 'Módulos'];
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
            $this->emit('show-toast', 'No tiene permisos para acceder a estos registros.', 'danger');
            return redirect()->to('/');
        }
    }

    public function query()
    {
        $query = match ($this->filter) {
            'Activos' => DB::table('tb_paquetes as p')->whereNull('p.deleted_at'),
            'Inactivos' => DB::table('tb_paquetes as p')->whereNotNull('p.deleted_at'),
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

        $query->leftJoin('tb_paquete_modulos as pm', 'p.id', 'pm.paquete_id')
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
            case 'Nombre':
                if ($this->order == 'asc')
                    $query->orderBy('p.nombre');
                else
                    $query->orderByDesc('p.nombre');
            case 'Descripción':
                if ($this->order == 'asc')
                    $query->orderBy('p.descripcion');
                else
                    $query->orderByDesc('p.descripcion');
            case 'Precio':
                if ($this->order == 'asc')
                    $query->orderBy('p.precio');
                else
                    $query->orderByDesc('p.precio');
            case 'Módulos':
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
