<?php

namespace App\Http\Livewire\Reportes\HistoricoOperaciones;

use App\Models\Sucursal;
use App\Models\Terminal;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    public $fecha_inicio;
    public $fecha_fin;
    public $sucursales_query;
    public $terminales_query;
    public $sucursales = [];
    public $terminales = [];
    public $terminalesDisponibles = [];

    public $perPage;
    public $perPages = [10, 25, 50, 100];
    public $search;
    public $order;
    public $sort;
    public $sorts = ['No. Ticket', 'Fecha', 'Cliente', 'Sucursal', 'Terminal', 'Empleado', 'Ubicación', 'Productos', 'Pagos', 'Departamentos', 'Importe'];

    protected $queryString = [
        'search' => ['except' => null],
        'order' => ['except' => null],
        'perPage' => ['except' => null],
        'sort' => ['except' => null],
        'fecha_inicio' => ['except' => null],
        'fecha_fin' => ['except' => null],
        'sucursales_query' => ['except' => null],
        'terminales_query' => ['except' => null]
    ];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->search = $this->search ?? '';
        $this->perPage = $this->perPage ?? 10;
        $this->order = $this->order ?? 'desc';
        $this->sort = $this->sort ?? 'Fecha';

        if ($this->sucursales_query) {
            $this->sucursales = explode(',', $this->sucursales_query);
            $this->loadTerminales();
        }
        if ($this->terminales_query)
            $this->terminales = explode(',', $this->terminales_query);
    }

    public function hydrate()
    {
        $this->dispatchBrowserEvent('reApplySelect2');
    }

    public function updated($field)
    {
        if ($field == 'sucursales')
            $this->loadTerminales();
        $this->dispatchBrowserEvent('reApplySelect2');
    }

    public function render()
    {
        $tickets = $this->query();
        $total = $tickets->count();
        $records = $tickets->forPage($this->page, $this->perPage);
        $tickets = new LengthAwarePaginator($records, $total, $this->perPage, $this->page);
        return view('livewire.reportes.historico-operaciones.index', [
            'tickets' => $tickets,
            'sucursalesDisponibles' => Sucursal::where('cliente_id', user()->cliente_id)->whereIn('id', user()->sucursales->pluck('id')->toArray())->get()->map(function ($value) {
                return [
                    'value' => $value->id,
                    'label' => Crypt::decrypt($value->nombre_comercial)
                ];
            })->toArray()
        ]);
    }

    public function getClassSortProperty()
    {
        return $this->order == 'asc' ? 'bi bi-sort-up-alt' : 'bi bi-sort-down-alt';
    }

    public function query()
    {
        $query = DB::table('tb_tickets as t')
            ->select(
                't.id',
                't.id_transaccion',
                't.ubicacion',
                't.fecha_transaccion',
                DB::raw("DATE_FORMAT(t.fecha_transaccion, '%d/%m/%Y %H:%i') as fecha_transaccion_str"),
                'c.razon_social as cliente',
                's.razon_social as sucursal',
                'ter.identificador as terminal',
                'e.nombre as empleado',
                't.importe',
                DB::raw("(SELECT GROUP_CONCAT(CONCAT(p.nombre, ' (', tp.cantidad, ')')) from tb_ticket_productos as tp left join tb_productos as p on p.id = tp.producto_id where tp.ticket_id = t.id) as productos"),
                DB::raw("(SELECT GROUP_CONCAT(CONCAT(operacion.nombre, ' (', operacion.monto, ')')) from tb_ticket_operaciones as operacion where operacion.ticket_id = t.id) as pagos"),
                DB::raw("(SELECT GROUP_CONCAT(distinct d.nombre) from tb_ticket_productos as tp left join tb_departamentos as d on d.id = tp.departamento_id where tp.ticket_id = t.id) as departamentos")
            )
            ->leftJoin('tb_sucursales as s', 's.id', '=', 't.sucursal_id')
            ->leftJoin('tb_terminales as ter', 'ter.id', '=', 't.terminal_id')
            ->leftJoin('tb_empleados as e', 'e.id', '=', 't.empleado_id')
            ->leftJoin('tb_clientes as c', 'c.id', '=', 't.comensal_id')
            ->groupBy('t.id');

        if ($this->fecha_inicio)
            $query->whereDate('t.fecha_transaccion', '>=', $this->fecha_inicio);
        if ($this->fecha_fin)
            $query->whereDate('t.fecha_transaccion', '<=', $this->fecha_fin);
        if (count(Arr::wrap($this->sucursales)) > 0)
            $query->whereIn('s.id', $this->sucursales);
        else
            $query->whereIn('s.id', user()->sucursales->pluck('id')->toArray());
        if (count(Arr::wrap($this->terminales)) > 0)
            $query->whereIn('ter.id', $this->terminales);
        else
            $query->whereIn('ter.id', user()->terminales->pluck('id')->toArray());

        $tickets = $query->get()->map(function ($element) {
            return (array) $element;
        })->toArray();
        $records_final = collect();

        foreach ($tickets as $ticket) {
            $ticket['cliente'] = $ticket['cliente'] ? Crypt::decrypt($ticket['cliente']) : '';
            $ticket['sucursal'] = $ticket['sucursal'] ? Crypt::decrypt($ticket['sucursal']) : '';
            $ticket['empleado'] = $ticket['empleado'] ? Crypt::decrypt($ticket['empleado']) : '';

            if (
                !$this->search
                || Str::contains(Str::upper($ticket['id_transaccion']), Str::upper($this->search))
                || Str::contains(Str::upper($ticket['ubicacion']), Str::upper($this->search))
                || Str::contains(Str::upper($ticket['fecha_transaccion_str']), Str::upper($this->search))
                || Str::contains(Str::upper($ticket['cliente']), Str::upper($this->search))
                || Str::contains(Str::upper($ticket['sucursal']), Str::upper($this->search))
                || Str::contains(Str::upper($ticket['empleado']), Str::upper($this->search))
                || Str::contains(Str::upper($ticket['terminal']), Str::upper($this->search))
                || Str::contains(Str::upper($ticket['productos']), Str::upper($this->search))
                || Str::contains(Str::upper($ticket['pagos']), Str::upper($this->search))
                || Str::contains(Str::upper($ticket['departamentos']), Str::upper($this->search))
                || Str::contains(Str::upper($ticket['importe']), Str::upper($this->search))
            ) {
                $records_final->push($ticket);
            }
        }

        switch ($this->sort) {
            case 'No. Ticket':
                if ($this->sort == 'asc')
                    $records_final = $records_final->sortBy('id_transaccion', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('id_transaccion', SORT_NATURAL)->values();
                break;
            case 'Fecha':
                if ($this->sort == 'asc')
                    $records_final = $records_final->sortBy('fecha_transaccion', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('fecha_transaccion', SORT_NATURAL)->values();
                break;
            case 'Cliente':
                if ($this->sort == 'asc')
                    $records_final = $records_final->sortBy('cliente', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('cliente', SORT_NATURAL)->values();
                break;
            case 'Sucursal':
                if ($this->sort == 'asc')
                    $records_final = $records_final->sortBy('sucursal', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('sucursal', SORT_NATURAL)->values();
                break;
            case 'Terminal':
                if ($this->sort == 'asc')
                    $records_final = $records_final->sortBy('terminal', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('terminal', SORT_NATURAL)->values();
                break;
            case 'Empleado':
                if ($this->sort == 'asc')
                    $records_final = $records_final->sortBy('empleado', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('empleado', SORT_NATURAL)->values();
                break;
            case 'Ubicación':
                if ($this->sort == 'asc')
                    $records_final = $records_final->sortBy('ubicacion', SORT_NATURAL)->values();
                if ($this->sort == 'asc')
                    $records_final = $records_final->sortByDesc('ubicacion', SORT_NATURAL)->values();
                break;
            case 'Productos':
                if ($this->sort == 'asc')
                    $records_final = $records_final->sortBy('productos', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('productos', SORT_NATURAL)->values();
                break;
            case 'Pagos':
                if ($this->sort == 'asc')
                    $records_final = $records_final->sortBy('pagos', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('pagos', SORT_NATURAL)->values();
                break;
            case 'Departamentos':
                if ($this->sort == 'asc')
                    $records_final = $records_final->sortBy('departamentos', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('departamentos', SORT_NATURAL)->values();
                break;
            case 'Importe':
                if ($this->sort == 'asc')
                    $records_final = $records_final->sortBy('importe', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('importe', SORT_NATURAL)->values();
                break;
        }

        return $records_final;
    }

    public function loadTerminales()
    {
        $this->terminalesDisponibles = Terminal::whereIn('sucursal_id', Arr::wrap($this->sucursales))->whereIn('id', user()->terminales->pluck('id')->toArray())->get()->map->only(['value', 'label'])->toArray();
    }

    public function changeSort($sort)
    {
        $this->order = !$this->order || $this->sort != $sort ? 'asc' : ($this->order == 'asc' ? 'desc' : '');
        $this->sort = !$this->order ? '' : $sort;
    }
}
