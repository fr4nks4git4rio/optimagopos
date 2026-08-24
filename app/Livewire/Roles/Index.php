<?php

namespace App\Livewire\Roles;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $page;
    public $perPage;
    public $perPages = [10, 25, 50, 100];
    public $search;
    public $order;
    public $sort;
    public $sorts = [];

    protected $queryString = ['search', 'perPage', 'sort', 'order'];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->sorts = [__('site.roles.index.name')];

        $this->perPage = $this->perPage ?? 10;
        $this->search = $this->search ?? '';
        $this->order = $this->order ?? 'asc';
        $this->sort = $this->sort ?? __('site.roles.index.name');
    }

    public function getClassSortProperty()
    {
        return $this->order == 'asc' ? 'bi bi-sort-up-alt' : 'bi bi-sort-down-alt';
    }

    public function updated($field)
    {
        if (in_array($field, ['search', 'perPage', 'sort', 'order']))
            $this->resetPage();
    }

    public function render()
    {
        $records = $this->query();

        $currentPage = $this->getPage();
        $total = $records->count();
        $currentItems = $records->forPage($currentPage, $this->perPage)->values();

        $roles = new LengthAwarePaginator($currentItems, $total, $this->perPage, $currentPage, ['path' => LengthAwarePaginator::resolveCurrentPath()]);
        return view('livewire.roles.index', [
            'roles' => $roles,
        ]);
    }

    public function init()
    {
        if (user()->cannot('roles-viewAny')) {
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
            return redirect()->to('/');
        }
    }

    public function query()
    {
        $query = DB::table('roles')->select('id', 'name', 'guard_name')->whereIn('name', ['Admin', 'Manager']);

        if ($this->search) {
            $query->where(function ($query) {
                $query->orWhere('name', 'like', "%$this->search%");
            });
        }
        switch ($this->sort) {
            case __('site.roles.index.name'):
                if ($this->order == 'asc')
                    $query->orderBy('name');
                else
                    $query->orderByDesc('name');
        }
        return $query->get()->map(fn($value, $key) => (array) $value);
    }

    public function changeSort($sort)
    {
        $this->order = !$this->order || $this->sort != $sort ? 'asc' : ($this->order == 'asc' ? 'desc' : '');
        $this->sort = !$this->order ? '' : $sort;
    }
}
