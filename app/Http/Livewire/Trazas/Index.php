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
    public $order;
    public $sort;
    public $sorts = [];
    public $perPage;
    public $perPages = [10, 25, 50, 100];

    protected $queryString = ['search', 'sort', 'order', 'perPage'];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->sorts = [__('site.logs.index.name_log'), __('site.logs.index.description'), __('site.logs.index.performed_by'), __('site.logs.index.date')];
        $this->search = $this->search ?? '';
        $this->perPage = $this->perPage ?? 10;
        $this->order = $this->sort ?? 'desc';
        $this->sort = $this->sort ?? __('site.logs.index.date');
    }

    public function getClassSortProperty()
    {
        return $this->order == 'asc' ? 'bi bi-sort-up-alt' : 'bi bi-sort-down-alt';
    }

    public function render()
    {
        //        dd($this->query()->get());
        return view('livewire.trazas.index', [
            'trazas' => $this->query()->orderBy('created_at', 'desc')->paginate($this->perPage),
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
                DB::raw('CONCAT(CONCAT_WS(" ", user.nombre, user.apellidos), " (", user.email, ")") as causer_name'),
                'user.email as causer_username'
            )
            ->leftJoin('tb_usuarios as user', 'log.causer_id', '=', 'user.id');

        if (user()->cliente_id) {
            $query->where('user.cliente_id', user()->cliente_id);
        }

        if ($this->search) {
            $query->where(function (Builder $query) {
                $query->orWhere('log.description', 'like', '%' . $this->search . '%')
                    ->orWhere('log.log_name', 'like', '%' . $this->search . '%')
                    ->orWhere('log.created_at', 'like', '%' . $this->search . '%')
                    ->orWhere('user.email', 'like', '%' . $this->search . '%')
                    ->orWhereRaw('CONCAT_WS(" ", user.first_name, user.last_name) like ?', ['%' . $this->search . '%']);
            });
        }

        switch ($this->sort) {
            case __('site.logs.index.name_log'):
                if ($this->order == 'asc')
                    $query->orderBy('log.log_name');
                else
                    $query->orderByDesc('log.log_name');
                break;
            case __('site.logs.index.description'):
                if ($this->order == 'asc')
                    $query->orderBy('log.description');
                else
                    $query->orderByDesc('log.description');
                break;
            case __('site.logs.index.performed_by'):
                if ($this->order == 'asc')
                    $query->orderByRaw("CONCAT_WS(' ', user.first_name, user.last_name) asc");
                else
                    $query->orderByRaw("CONCAT_WS(' ', user.first_name, user.last_name) desc");
                break;
            case __('site.logs.index.date'):
                if ($this->order == 'asc')
                    $query->orderBy('log.created_at');
                else
                    $query->orderByDesc('log.created_at');
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
