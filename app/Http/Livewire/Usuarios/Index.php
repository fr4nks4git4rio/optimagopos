<?php

namespace App\Http\Livewire\Usuarios;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
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

    protected $queryString = ['search', 'perPage', 'order', 'sort', 'filter'];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        if (user()->is_super_admin)
            $this->sorts = ['Nombre Completo', 'Correo', 'Cliente'];
        else
            $this->sorts = ['Nombre Completo', 'Correo'];
        $this->order = $this->order ?? 'asc';
        $this->sort = $this->sort ?? 'Nombre Completo';
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
        $usuarios = $this->query();
        $total = $usuarios->count();
        $records = $usuarios->forPage($this->page, $this->perPage);
        $usuarios = new LengthAwarePaginator($records, $total, $this->perPage, $this->page);
        return view('livewire.usuarios.index', [
            'usuarios' => $usuarios,
        ]);
    }

    public function init()
    {
        if (user()->cannot('viewAny', [User::class])) {
            $this->emit('show-toast', 'No tiene permisos para acceder a estos registros.', 'danger');
            return redirect()->to('/');
        }
    }

    public function query()
    {
        $query = DB::table('tb_usuarios as u')
            ->select(
                'u.id',
                'u.avatar',
                DB::raw("CONCAT_WS(' ', u.nombre, u.apellidos) as nombre"),
                'u.email',
                'c.nombre_comercial as cliente',
                'u.cliente_id',
                'u.deleted_at'
            )
            ->leftJoin('tb_clientes as c', 'c.id', '=', 'u.cliente_id');

        if (!user()->is_super_admin)
            $query->where('cliente_id', user()->cliente_id);

        switch ($this->filter) {
            case 'Activos':
                $query->where('u.deleted_at', null);
                break;
            case 'Inactivos':
                $query->where('u.deleted_at', '!=', null);
                break;
            default:
                $query->where('u,.id', '>', 0);
                break;
        }

        if (!user()->is_super_admin) {
            $query->where('u.cliente_id', user()->cliente_id);
        }

        $usuarios = $query->get()->map(function ($element) {
            return (array) $element;
        })->toArray();
        $records_final = collect();

        foreach ($usuarios as $usuario) {
            $usuario['cliente'] = $usuario['cliente'] ? Crypt::decrypt($usuario['cliente']) : '';

            if (
                !$this->search
                || Str::contains(Str::upper($usuario['nombre']), Str::upper($this->search))
                || Str::contains(Str::upper($usuario['email']), Str::upper($this->search))
                || Str::contains(Str::upper($usuario['cliente']), Str::upper($this->search))
            ) {
                $records_final->push($usuario);
            }
        }

        switch ($this->sort) {
            case 'Nombre Completo':
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('nombre', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('nombre', SORT_NATURAL)->values();
                break;
            case 'Correo':
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('email', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('email', SORT_NATURAL)->values();
                break;
            case 'Cliente':
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('cliente', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('cliente', SORT_NATURAL)->values();
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
