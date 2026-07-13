<?php

namespace App\Http\Livewire\Cuarentena;

use App\Models\Cliente;
use App\Models\Sucursal;
use App\Models\Terminal;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class Index extends Component
{
    use WithPagination;

    public $cliente;
    public $sucursal;
    public $terminal;
    public $search;
    public $order;
    public $sort;
    public $perPage;
    public $perPages = [10, 25, 50, 100];
    public $sorts = [];
    public $clientes = [];
    public $sucursales = [];
    public $terminales = [];

    protected $queryString = [
        'search' => ['except' => null],
        'page' => ['except' => null],
        'perPage' => ['except' => null],
        'order' => ['except' => null],
        'sort' => ['except' => null],
        'cliente' => ['except' => null],
        'sucursal' => ['except' => null],
        'terminal' => ['except' => null]
    ];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->page = $this->page ?? 1;
        $this->perPage = $this->perPage ?? 10;
        $this->sorts = [__('site.quarantine.index.date'), __('site.quarantine.index.text'), __('site.quarantine.index.ip'), __('site.quarantine.index.client'), __('site.quarantine.index.branch'), __('site.quarantine.index.terminal')];
        $this->search = $this->search ?? '';
        $this->sort = $this->sort ?? __('site.quarantine.index.date');
        $this->order = $this->order ?? 'dec';

        $this->clientes = Cliente::all()->lazy()->map(function ($value) {
            return ['value' => $value->id, 'label' => Crypt::decrypt($value->nombre_comercial)];
        })->toArray();
        if ($this->cliente)
            $this->sucursales = Sucursal::where('cliente_id', $this->clinete)->lazy()->map(function ($value) {
                return ['value' => $value->id, 'label' => Crypt::decrypt($value->nombre_comercial)];
            })->toArray();
        if ($this->sucursal)
            $this->terminales = Terminal::where('sucursal_id', $this->sucursal)->lazy()->map->only(['value', 'label']);
    }

    public function updated($field, $value)
    {
        if ($value == 'cliente') {
            $this->sucursal = null;
            $this->terminal = null;
            $this->sucursales = [];
            $this->terminales = [];

            $this->sucursales = Sucursal::where('cliente_id', $this->clinete)->lazy()->map(function ($value) {
                return ['value' => $value->id, 'label' => Crypt::decrypt($value->nombre_comercial)];
            });
        }
        if ($field == 'sucursal') {
            $this->terminal = null;
            $this->terminales = [];

            $this->terminales = Terminal::where('sucursal_id', $this->sucursal)->lazy()->map->only(['value', 'label']);
        }
    }

    public function getClassSortProperty()
    {
        return $this->order == 'asc' ? 'bi bi-sort-up-alt' : 'bi bi-sort-down-alt';
    }

    public function render()
    {
        $records = $this->query()->get()->each(function ($value) {
            $value->cliente = $value->cliente ? Crypt::decrypt($value->cliente) : '';
            $value->sucursal = $value->sucursal ? Crypt::decrypt($value->sucursal) : '';
            return $value;
        });

        switch ($this->sort) {
            case __('site.quarantine.index.date'):
                if ($this->order == 'asc')
                    $records = $records->sortBy('fecha', SORT_NATURAL)->values();
                else
                    $records = $records->sortByDesc('fecha', SORT_NATURAL)->values();
                break;
            case __('site.quarantine.index.text'):
                if ($this->order == 'asc')
                    $records = $records->sortBy('texto', SORT_NATURAL)->values();
                else
                    $records = $records->sortByDesc('texto', SORT_NATURAL)->values();
                break;
            case __('site.quarantine.index.ip'):
                if ($this->order == 'asc')
                    $records = $records->sortBy('ip', SORT_NUMERIC)->values();
                else
                    $records = $records->sortByDesc('ip', SORT_NUMERIC)->values();
                break;
            case __('site.quarantine.index.client'):
                if ($this->order == 'asc')
                    $records = $records->sortBy('cliente', SORT_NATURAL)->values();
                else
                    $records = $records->sortByDesc('cliente', SORT_NATURAL)->values();
                break;
            case __('site.quarantine.index.branch'):
                if ($this->order == 'asc')
                    $records = $records->sortBy('sucursal', SORT_NATURAL)->values();
                else
                    $records = $records->sortByDesc('sucursal', SORT_NATURAL)->values();
                break;
            case __('site.quarantine.index.terminal'):
                if ($this->order == 'asc')
                    $records = $records->sortBy('terminal', SORT_NATURAL)->values();
                else
                    $records = $records->sortByDesc('terminal', SORT_NATURAL)->values();
                break;
        }

        $total = $records->count();
        $records  = $records->forPage($this->page, $this->perPage);
        $records = new LengthAwarePaginator($records, $total, $this->perPage, $this->page);
        return view('livewire.cuarentena.index', [
            'tickets' => $records,
        ]);
    }

    public function query()
    {
        //        $query = Activity::query();
        $query = DB::table('tb_cuarentena as ticket')
            ->select(
                'ticket.id',
                DB::raw("DATE_FORMAT(ticket.created_at, '%d/%m/%Y %H:%i:%s') as fecha"),
                'ticket.texto',
                'ticket.ip',
                'ticket.data as ticket',
                'ticket.cliente_id',
                'ticket.sucursal_id',
                'ticket.terminal_id',
                'cliente.nombre_comercial as cliente',
                'sucursal.nombre_comercial as sucursal',
                DB::raw("CONCAT_WS(' - ', terminal.nombre, terminal.identificador) as terminal"),
                'ticket.created_at'
            )
            ->leftJoin('tb_clientes as cliente', 'ticket.cliente_id', '=', 'cliente.id')
            ->leftJoin('tb_sucursales as sucursal', 'ticket.sucursal_id', '=', 'sucursal.id')
            ->leftJoin('tb_terminales as terminal', 'ticket.terminal_id', '=', 'terminal.id');

        if ($this->search) {
            $query->where(function (Builder $query) {
                $query->orWhere('ticket.texto', 'like', '%' . $this->search . '%')
                    ->orWhere('ticket.ip', 'like', '%' . $this->search . '%')
                    ->orWhere('texto.data', 'like', '%' . $this->search . '%')
                    ->orWhereRaw("DATE_FORMAT(ticket.created_at, '%d/%m/%Y %H:%i:%s') like ?", ["%$this->search%"]);
            });
        }

        return $query;
    }

    public function changeSort($sort)
    {
        $this->order = !$this->order || $this->sort != $sort ? 'asc' : ($this->order == 'asc' ? 'desc' : '');
        $this->sort = !$this->order ? '' : $sort;
    }
}
