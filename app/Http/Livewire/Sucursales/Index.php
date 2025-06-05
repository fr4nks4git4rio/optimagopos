<?php

namespace App\Http\Livewire\Sucursales;

use App\Models\Sucursal;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
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
    public $sorts = ['Nombre Comercial', 'RFC', 'Razón Social', 'Teléfono', 'Cliente'];
    public $filter = 'Activos';
    public $filters = ['Activos', 'Inactivos', 'Todos'];

    protected $queryString = ['search', 'perPage', 'sort', 'filter'];

    protected $listeners = ['$refresh'];

    public function render()
    {
        $sucursales = $this->query();
        $total = $sucursales->count();
        $records = $sucursales->forPage($this->page, $this->perPage);
        $sucursales = new LengthAwarePaginator($records, $total, $this->perPage, $this->page);
        return view('livewire.sucursales.index', [
            'sucursales' => $sucursales,
        ]);
    }

    public function query()
    {
        $query = DB::table('tb_sucursales as s')
            ->select(
                's.id',
                's.logo',
                's.nombre_comercial',
                's.rfc',
                's.razon_social',
                's.telefono',
                's.deleted_at',
                'c.nombre_comercial as cliente',
                's.deleted_at',
            )
            ->leftJoin('tb_clientes as c', 'c.id', '=', 's.cliente_id');

        switch ($this->filter) {
            case 'Activos':
                $query->where('s.deleted_at', null);
                break;
            case 'Inactivos':
                $query->where('s.deleted_at', '!=', null);
                break;
            default:
                $query->where('s.id', '>', 0);
        }

        if (user()->is_admin) {
            $query->where('s.cliente_id', user()->cliente_id);
        }

        $sucursales = $query->get()->map(function ($element) {
            return (array) $element;
        })->toArray();
        $records_final = collect();

        foreach ($sucursales as $sucursal) {
            $sucursal['cliente'] = $sucursal['cliente'] ? Crypt::decrypt($sucursal['cliente']) : '';
            $sucursal = Sucursal::decryptInfo($sucursal);

            if (
                !$this->search
                || Str::contains(Str::upper($sucursal['nombre_comercial']), Str::upper($this->search))
                || Str::contains(Str::upper($sucursal['rfc']), Str::upper($this->search))
                || Str::contains(Str::upper($sucursal['razon_social']), Str::upper($this->search))
                || Str::contains(Str::upper($sucursal['telefono']), Str::upper($this->search))
                || Str::contains(Str::upper($sucursal['cliente']), Str::upper($this->search))
            ) {
                $records_final->push($sucursal);
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
            case 'Cliente':
                $records_final = $records_final->sortBy('cliente', SORT_NATURAL)->values();
                break;
        }

        return $records_final;
    }
}
