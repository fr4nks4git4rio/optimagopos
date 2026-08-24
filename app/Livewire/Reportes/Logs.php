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
    public $order;
    public $sort;
    public $sorts;

    protected $queryString = ['search', 'fechaInicio', 'fechaFin', 'perPage', 'sort', 'order'];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->sorts = [
            __('site.reports.data_received.date'),
            __('site.reports.data_received.message'),
            __('site.reports.data_received.data'),
            __('site.reports.data_received.status'),
        ];
        $this->page ??= 1;
        $this->perPage ??= 10;
        $this->search ??= '';
        $this->order ??= 'desc';
        $this->sort ??= __('site.reports.data_received.date');
        $this->fechaInicio ??= today()->format('Y-m-d');
        $this->fechaFin ??= today()->format('Y-m-d');
    }

    public function updated($field)
    {
        if (in_array($field, ['search', 'fechaInicio', 'fechaFin', 'perPage', 'sort', 'order']))
            $this->resetPage();
    }

    public function getClassSortProperty()
    {
        return $this->order == 'asc' ? 'bi bi-sort-up-alt' : 'bi bi-sort-down-alt';
    }

    public function init()
    {
        if (user()->cannot('reportsDataReceived-viewAny')) {
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            return redirect()->to('/');
        }
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
            )
            ->groupBy('log.id');

        if (user()->hasAnyRole(['Admin', 'Manager'])) {
            $query->join('tb_sucursales as sucursal', 'sucursal.id', 'log.sucursal_id')
                ->where('sucursal.cliente_id', user()->cliente_id)
                ->whereIn('sucursal.id', user()->sucursales->pluck('id')->toArray());
        }

        if ($this->fechaInicio) {
            $query->whereDate('log.created_at', '>=', $this->fechaInicio);
        }

        if ($this->fechaFin) {
            $query->whereDate('log.created_at', '<=', $this->fechaFin);
        }

        if ($this->search) {
            $query->where('log.log', 'like', "%$this->search%")
                ->orWhereRaw("log.data like ?", ["%$this->search%"])
                ->orWhere('log.status', 'like', "%$this->search%");
        }

        switch ($this->sort) {
            case __('site.reports.data_received.date'):
                if ($this->order == 'desc')
                    $query->orderByDesc('log.created_at');
                else
                    $query->orderBy('log.created_at');
                break;
            case __('site.reports.data_received.message'):
                if ($this->order == 'desc')
                    $query->orderBy('log.log');
                else
                    $query->orderByDesc('log.log');
                break;
            case __('site.reports.data_received.data'):
                if ($this->order == 'desc')
                    $query->orderByDesc('log.data');
                else
                    $query->orderBy('log.data');
                break;
            case __('site.reports.data_received.status'):
                if ($this->order == 'desc')
                    $query->orderByDesc('log.status');
                else
                    $query->orderBy('log.status');
                break;
        }

        return $query;
    }

    public function changeSort($sort)
    {
        $this->order = !$this->order || $this->sort != $sort ? 'asc' : ($this->order == 'asc' ? 'desc' : '');
        $this->sort = !$this->order ? '' : $sort;
    }
}
