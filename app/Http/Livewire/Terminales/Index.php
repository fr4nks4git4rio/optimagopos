<?php

namespace App\Http\Livewire\Terminales;

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

    public $perPage;
    public $perPages;
    public $search;
    public $order;
    public $sort;
    public $sorts;
    public $filter;
    public $filters;

    protected $queryString = ['search', 'order', 'sort', 'filter', 'perPage'];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->search = $this->search ?? '';
        $this->order = $this->order ?? 'asc';
        $this->sort = $this->sort ?? 'Identificador';
        $this->sorts = ['Identificador', 'Nombre', 'Sucursal', 'Comentarios'];
        $this->filter = $this->filter ?? 'Activos';
        $this->filters = ['Activos', 'Inactivos', 'Todos'];
        $this->perPage = $this->perPage ?? 10;
        $this->perPages = [10, 25, 50, 100];
    }

    public function getClassSortProperty()
    {
        return $this->order == 'asc' ? 'bi bi-sort-up-alt' : 'bi bi-sort-down-alt';
    }

    public function render()
    {
        $terminales = $this->query();
        $total = $terminales->count();
        $records = $terminales->forPage($this->page, $this->perPage);
        $terminales = new LengthAwarePaginator($records, $total, $this->perPage, $this->page);
        return view('livewire.terminales.index', [
            'terminales' => $terminales,
        ]);
    }

    public function init()
    {
        if (user()->cannot('viewAny', [Terminal::class])) {
            $this->emit('show-toast', 'No tiene permisos para visualizar los registros.', 'danger');
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
                't.comentarios',
                't.deleted_at',
                's.nombre_comercial as sucursal'
            )
            ->leftJoin('tb_sucursales as s', 's.id', '=', 't.sucursal_id');

        if (user()->cliente_id) {
            $query->where('s.cliente_id', user()->cliente_id);
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

        $terminales = $query->get()->map(function ($element) {
            return (array) $element;
        });
        $records_final = collect();

        foreach ($terminales as $terminal) {
            $terminal['sucursal'] = $terminal['sucursal'] ? Str::upper(Crypt::decrypt($terminal['sucursal'])) : '';

            if (
                !$this->search
                || Str::contains(Str::upper($terminal['identificador']), Str::upper($this->search))
                || Str::contains(Str::upper($terminal['nombre']), Str::upper($this->search))
                || Str::contains(Str::upper($terminal['comentarios']), Str::upper($this->search))
                || Str::contains(Str::upper($terminal['sucursal']), Str::upper($this->search))
            ) {
                $records_final->push($terminal);
            }
        }

        switch ($this->sort) {
            case 'Identificador':
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('identificador', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('identificador', SORT_NATURAL)->values();
                break;
            case 'Nombre':
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('nombre', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('nombre', SORT_NATURAL)->values();
                break;
            case 'Sucursal':
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('sucursal', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('sucursal', SORT_NATURAL)->values();
                break;
            case 'Comentarios':
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
