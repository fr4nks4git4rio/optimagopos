<?php

namespace App\Http\Livewire\Clientes;

use App\Models\Cliente;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $perPage;
    public $perPages = [10, 25, 50, 100];
    public $search;
    public $order;
    public $sort;
    public $sorts = ['Nombre Comercial', 'RFC', 'Razón Social', 'Teléfono'];
    public $filter;
    public $filters = ['Activos', 'Inactivos', 'Todos'];

    protected $queryString = ['search', 'perPage', 'sort', 'order', 'filter'];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->perPage = $this->perPage ?? 10;
        $this->search = $this->search ?? '';
        $this->order = $this->order ?? 'asc';
        $this->sort = $this->sort ?? 'Nombre Comercial';
        $this->filter = $this->filter ?? 'Activos';
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
        return view('livewire.clientes.index', [
            'clientes' => $clientes,
        ]);
    }

    public function init()
    {
        if (user()->cannot('viewAnyCliente', [Cliente::class])) {
            $this->emit('show-toast', 'No tiene permisos para acceder a estos registros.', 'danger');
            return redirect()->to('/');
        }
    }

    public function query()
    {
        $query = match ($this->filter) {
            'Activos' => Cliente::withoutTrashed(),
            'Inactivos' => Cliente::onlyTrashed(),
            default => Cliente::withTrashed(),
        };

        $clientes = $query->where('es_cliente', 1)->get()->map->only(['id', 'nombre_comercial', 'rfc', 'razon_social', 'telefono', 'deleted_at'])->toArray();
        $records_final = collect();

        foreach ($clientes as $cliente) {
            $cliente = Cliente::decryptInfo($cliente);

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
            case 'Nombre Comercial':
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('nombre_comercial', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('nombre_comercial', SORT_NATURAL)->values();
                break;
            case 'RFC':
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('rfc', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('rfc', SORT_NATURAL)->values();
                break;
            case 'Razón Social':
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('razon_social', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('razon_social', SORT_NATURAL)->values();
                break;
            case 'Teléfono':
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('telefono', SORT_NATURAL)->values();
                else
                    $records_final = $records_final->sortByDesc('telefono', SORT_NATURAL)->values();
                break;
        }

        return $records_final;
    }

    public function gestionarSuscripcion($id)
    {
        $cliente = Cliente::with('direccion_fiscal')->find($id);
        if (!$cliente->es_cliente) {
            $this->emit('show-toast', 'Cliente no encontrado!', 'danger');
            return;
        }

        $cliente =  Cliente::decryptInfo($cliente);
        $validator = Validator::make($cliente->toArray(), [
            'razon_social' => 'required',
            'rfc' => 'required',
            'contacto_nombre' => 'required',
            'contacto_correo' =>  'required|email',
            'contacto_telefono' => 'required',
            'direccion_fiscal'  => 'required',
            'direccion_fiscal.codigo_postal'  => 'required',
            'regimen_fiscal_id' => 'required|exists:tb_regimen_fiscales,id'
        ], [
            'razon_social.required' => 'Entre la Razón Social.',
            'rfc.required' => 'Entre el RFC.',
            'contacto_nombre.required' => 'Entre el nombre del contacto.',
            'contacto_correo.required' => 'Entre el correo del contacto.',
            'contacto_telefono.required' => 'Entre el teléfono del contacto.',
            'direccion_fiscal.required' => 'Defina la dirección fiscal del cliente.',
            'direccion_fiscal.codigo_postal.required' => 'Entre el código postal.',
            'regimen_fiscal_id.required' => 'Entre el régimen fiscal.',
            'regimen_fiscal_id.exists' => 'El régimen fiscal del cliente no es correcto.'
        ]);

        if ($validator->fails()) {
            $messages = Arr::map(Arr::flatten($validator->messages()->messages()), function ($value) {
                return [
                    'type' => 'danger',
                    'text' => $value
                ];
            });
            $this->emit('openModal', 'modal-toast', ['messages' => $messages]);
            return;
        }

        return redirect()->route('admin.clientes.suscripcion', $id);
    }

    public function changeSort($sort)
    {
        $this->order = !$this->order || $this->sort != $sort ? 'asc' : ($this->order == 'asc' ? 'desc' : '');
        $this->sort = !$this->order ? '' : $sort;
    }
}
