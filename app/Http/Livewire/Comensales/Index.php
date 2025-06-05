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
    public $sort = 'Nombre Comercial';
    public $sorts = ['Nombre Comercial', 'RFC', 'Razón Social', 'Teléfono'];
    public $filter = 'Activos';
    public $filters = ['Activos', 'Inactivos', 'Todos'];

    protected $queryString = ['search', 'perPage', 'sort', 'filter'];

    protected $listeners = ['$refresh'];

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

    public function query()
    {
        $query = match ($this->filter) {
            'Activos' => Cliente::withoutTrashed(),
            'Inactivos' => Cliente::onlyTrashed(),
            default => Cliente::withTrashed(),
        };

        $clientes = $query->where('es_comensal', 1)->get()->map->only('id', 'nombre_comercial', 'rfc', 'razon_social', 'telefono', 'deleted_at')->toArray();
        $records_final = collect();

        foreach ($clientes as $cliente) {
            $cliente = Cliente::decryptInfo($cliente);

            if (!$this->search
                || Str::contains(Str::upper($cliente['nombre_comercial']), Str::upper($this->search))
                || Str::contains(Str::upper($cliente['rfc']), Str::upper($this->search))
                || Str::contains(Str::upper($cliente['razon_social']), Str::upper($this->search))
                || Str::contains(Str::upper($cliente['telefono']), Str::upper($this->search))
            ) {
                $records_final->push($cliente);
            }
        }

        switch ($this->sort) {
            case 'Nombre Comercial':
                $records_final = $records_final->sortBy('nombre_comercial', SORT_NATURAL)->values();
                break;
            case 'RFC':
                $records_final = $records_final->sortBy('rfc', SORT_NATURAL)->values();
                break;
            case 'Razón Social':
                $records_final = $records_final->sortBy('razon_social', SORT_NATURAL)->values();
                break;
            case 'Teléfono':
                $records_final = $records_final->sortBy('telefono', SORT_NATURAL)->values();
                break;
        }

        return $records_final;
    }
}
