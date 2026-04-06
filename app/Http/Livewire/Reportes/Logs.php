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
        $query = DB::table('tb_logs')
            ->select(
                'id',
                DB::raw("DATE_FORMAT(created_at, '%d/%m/%Y %H:%i:%s') as fecha"),
                'log',
                'data',
                'status'
            );

        if ($this->search) {
            $query->where('log', 'like', "%$this->search%")
                ->orWhereRaw("data like ?", ["%$this->search%"])
                ->orWhere('status', 'like', "%$this->search%");
        }

        switch ($this->sort) {
            case 'Fecha':
                $query->orderBy('created_at', 'desc');
                break;
            case 'Log':
                $query->orderBy('log');
                break;
            case 'Datos':
                $query->orderBy('data');
                break;
            case 'Estado':
                $query->orderBy('status');
                break;
        }

        return $query;
    }
}
