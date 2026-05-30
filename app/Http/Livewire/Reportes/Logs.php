<?php

namespace App\Http\Livewire\Reportes;

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

    public $perPage = 10;
    public $perPages = [10, 25, 50, 100];
    public $search;
    public $sort = 'Fecha';
    public $sorts = ['Fecha', 'Log', 'Datos', 'Estado'];

    protected $queryString = ['search', 'perPage', 'sort'];

    protected $listeners = ['$refresh'];

    public function render()
    {
        $logs = $this->query();
        $total = $logs->count();
        $records = $logs->forPage($this->page, $this->perPage);
        $logs = new LengthAwarePaginator($records, $total, $this->perPage, $this->page);
        return view('livewire.reportes.logs', [
            'logs' => $this->query()->paginate($this->perPage),
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

        if(!user()->is_super_admin){
            $query->join('tb_sucursales as sucursal', 'sucursal.id', 'log.sucursal_id')
                ->where('sucursal.cliente_id', user()->cliente_id);
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
