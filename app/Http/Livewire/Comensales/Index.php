<?php

namespace App\Http\Livewire\Comensales;

use App\Models\Cliente;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $perPages = [10, 25, 50, 100];
    public $search;
    public $order;
    public $sort;
    public $sorts = [];
    public $filter;
    public $filters = [];

    protected $queryString = ['search', 'perPage', 'sort', 'filter'];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->sorts = [__('site.diners.index.commercial_name'), __('site.diners.index.rfc'), __('site.diners.index.social_reason'), __('site.diners.index.phone'), __('site.diners.index.status')];
        $this->filters = [__('site.common.actives'), __('site.common.inactives'), __('site.common.all')];
        $this->search = $this->search ?? '';
        $this->perPage = $this->perPage ?? '';
        $this->order = $this->order ?? 'asc';
        $this->sort = $this->sort ?? __('site.diners.index.commercial_name');
        $this->filter = $this->filter ?? __('site.common.actives');
    }

    public function getClassSortProperty()
    {
        return $this->order == 'asc' ? 'bi bi-sort-up-alt' : 'bi bi-sort-down-alt';
    }

    public function render()
    {
        $clientes = $this->query();
        $total = $clientes->count();
        $records = $clientes->forPage($this->page, $this->perPage);
        $clientes = new LengthAwarePaginator($records, $total, $this->perPage, $this->page);
        return view('livewire.comensales.index', [
            'clientes' => $clientes,
        ]);
    }

    public function init()
    {
        if (user()->cannot('viewAnyComensal', [Cliente::class])) {
            $this->emit('show-toast', 'No tiene permisos para acceder a estos registros.', 'danger');
            return redirect()->to('/');
        }
    }

    public function query()
    {
        $query = match ($this->filter) {
            __('site.common.actives') => Cliente::find(user()->cliente_id)->comensales_activos(),
            __('site.common.inactives') => user()->cliente->comensales_inactivos(),
            default => user()->cliente->comensales(),
        };

        $clientes = $query->get()->map(function ($value) {
            Cliente::decryptInfo($value);
            return [
                'id' => $value->id,
                'nombre_comercial' => $value->nombre_comercial,
                'rfc' => $value->rfc,
                'razon_social' => $value->razon_social,
                'telefono' => $value->telefono,
                'activo' => $value->pivot->activo
            ];
        })->toArray();

        $records_final = collect();

        foreach ($clientes as $cliente) {
            if (
                !$this->search
                || Str::contains(Str::upper($cliente['nombre_comercial']), Str::upper($this->search))
                || Str::contains(Str::upper($cliente['rfc']), Str::upper($this->search))
                || Str::contains(Str::upper($cliente['razon_social']), Str::upper($this->search))
                || Str::contains(Str::upper($cliente['telefono']), Str::upper($this->search))
            ) {
                $records_final->push($cliente);
            }
        }

        switch ($this->sort) {
            case __('site.diners.index.commercial_name'):
                if ($this->order ==  'asc')
                    $records_final = $records_final->sortBy('nombre_comercial', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('nombre_comercial', SORT_NATURAL)->values();
                break;
            case __('site.diners.index.rfc'):
                if ($this->order ==  'asc')
                    $records_final = $records_final->sortBy('rfc', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('rfc', SORT_NATURAL)->values();
                break;
            case __('site.diners.index.social_reason'):
                if ($this->order ==  'asc')
                    $records_final = $records_final->sortBy('razon_social', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('razon_social', SORT_NATURAL)->values();
                break;
            case __('site.diners.index.phone'):
                if ($this->order ==  'asc')
                    $records_final = $records_final->sortBy('telefono', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('telefono', SORT_NATURAL)->values();
                break;
            case __('site.diners.index.status'):
                if ($this->order ==  'asc')
                    $records_final = $records_final->sortBy('activo', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('activo', SORT_NATURAL)->values();
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
