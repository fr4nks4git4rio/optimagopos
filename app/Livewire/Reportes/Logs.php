<?php

namespace App\Livewire\Reportes;

use App\Models\Sucursal;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Logs extends Component
{
    use WithPagination;

    public $page;
    public $perPage;
    public $perPages = [10, 25, 50, 100];
    public $fechaInicio;
    public $fechaFin;
    public $search;
    public $sort;
    public $sorts = ['Fecha', 'Log', 'Datos', 'Estado'];

    protected $queryString = ['search', 'fechaInicio', 'fechaFin', 'perPage', 'sort', 'page'];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->page ??= 1;
        $this->perPage ??= 10;
        $this->search ??= '';
        $this->sort ??= 'Fecha';
        $this->fechaInicio ??= today()->format('Y-m-d');
        $this->fechaFin ??= today()->format('Y-m-d');
    }

    public function updated($field)
    {
        if (in_array($field, ['search', 'fechaInicio', 'fechaFin', 'perPage', 'sort']))
            $this->resetPage();
    }

    public function render()
    {
        $records = $this->query()->get();

        $currentPage = $this->getPage();
        $total = $records->count();
        $currentItems = $records->forPage($currentPage, $this->perPage)->values();

        $logs = new LengthAwarePaginator($currentItems, $total, $this->perPage, $currentPage, ['path' => LengthAwarePaginator::resolveCurrentPath()]);
        return view('livewire.reportes.logs', [
            'logs' => $logs,
        ]);
    }

    public function query()
    {
        $query = DB::table('tb_logs as log')
            ->select(
                'log.id',
                DB::raw("DATE_FORMAT(log.created_at, '%d/%m/%Y %H:%i:%s') as fecha"),
                'log.log',
                'log.data',
                'log.status'
            );

        if (user()->cliente_id) {
            $query->join('tb_sucursales as sucursal', 'sucursal.id', 'log.sucursal_id')
                ->where('sucursal.cliente_id', user()->cliente_id)
                ->whereIn('sucursal.id', user()->sucursales->pluck('id')->toArray());
        }

        if($this->fechaInicio){
            $query->whereDate('log.created_at', '>=', $this->fechaInicio);
        }

        if($this->fechaFin){
            $query->whereDate('log.created_at', '<=', $this->fechaFin);
        }

        if ($this->search) {
            $query->where('log.log', 'like', "%$this->search%")
                ->orWhereRaw("log.data like ?", ["%$this->search%"])
                ->orWhere('log.status', 'like', "%$this->search%");
        }

        switch ($this->sort) {
            case 'Fecha':
                $query->orderBy('log.created_at', 'desc');
                break;
            case 'Log':
                $query->orderBy('log.log');
                break;
            case 'Datos':
                $query->orderBy('log.data');
                break;
            case 'Estado':
                $query->orderBy('log.status');
                break;
        }

        return $query;
    }
}
