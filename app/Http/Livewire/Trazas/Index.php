<?php

namespace App\Http\Livewire\Trazas;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class Index extends Component
{
    use WithPagination;

    public $search;
    public $sort;
    public $sorts;
    public $filter;
    public $filters;

    protected $queryString = ['search', 'sort'];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->sort = 'Más Actuales';
        $this->sorts = ['Más Actuales', 'Descripción', 'Más Antiguos'];
        $this->filter = __('All');
        $this->filters = [__('All')];
    }

    public function render()
    {
        //        dd($this->query()->get());
        return view('livewire.trazas.index', [
            'trazas' => $this->query()->orderBy('created_at', 'desc')->paginate(),
        ]);
    }

    public function query()
    {
        //        $query = Activity::query();
        $query = DB::table('activity_log as log')
            ->select(
                'log.id',
                'log.description',
                'log.causer_type',
                'log.causer_id',
                'log.log_name',
                'log.subject_id',
                'log.subject_type',
                'log.created_at',
                DB::raw('CONCAT(user.nombre, " ", user.apellidos, " (", user.email, ")") as causer_name'),
                'user.email as causer_username'
            )
            ->leftJoin('tb_usuarios as user', 'log.causer_id', '=', 'user.id');

        if (user()->is_admin) {
            $query->where('user.rol_id', user()->rol_id);
        }

        if ($this->search) {
            $query->where(function (Builder $query) {
                $query->orWhere('log.description', 'like', '%' . $this->search . '%')
                    ->orWhere('log.log_name', 'like', '%' . $this->search . '%')
                    ->orWhere('log.created_at', 'like', '%' . $this->search . '%')
                    ->orWhere('user.email', 'like', '%' . $this->search . '%')
                    ->orWhereRaw('CONCAT(user.first_name, " ", user.last_name) like ?', ['%' . $this->search . '%']);
            });
        }

        switch ($this->sort) {
            case 'Más Actuales':
                $query->orderBy('log.created_at', 'desc');
                break;
            case 'Descripción':
                $query->orderBy('log.description');
                break;
            case 'Más Antiguos':
                $query->orderBy('log.created_at', 'asc');
                break;
        }

        switch ($this->filter) {
            case __('All'):
                $query->whereNotNull('log.id');
                break;
        }

        return $query;
    }
}
