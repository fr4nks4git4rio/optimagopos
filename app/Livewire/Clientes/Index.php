<?php

namespace App\Livewire\Clientes;

use App\Models\Cliente;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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
    public $filter;
    public $filters = [];

    protected $queryString = ['search', 'perPage', 'sort', 'order', 'filter', 'page'];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->sorts = [__('site.clients.index.commercial_name'), __('site.clients.index.rfc'), __('site.clients.index.social_reason'), __('site.clients.index.phone'), __('site.clients.index.status')];
        $this->filters = [__('site.common.actives'), __('site.common.inactives'), __('site.common.all')];
        $this->page ??= 1;
        $this->perPage = $this->perPage ?? 10;
        $this->search = $this->search ?? '';
        $this->order = $this->order ?? 'asc';
        $this->sort = $this->sort ?? __('site.clients.index.commercial_name');
        $this->filter = $this->filter ?? __('site.common.actives');
    }

    public function getClassSortProperty()
    {
        return $this->order == 'asc' ? 'bi bi-sort-up-alt' : 'bi bi-sort-down-alt';
    }

    public function updated($field)
    {
        if (in_array($field, ['filter', 'search', 'perPage', 'order', 'sort'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $records = $this->query();

        $currentPage = $this->getPage();
        $total = $records->count();
        $currentItems = $records->forPage($currentPage, $this->perPage)->values();

        $clientes = new LengthAwarePaginator($currentItems, $total, $this->perPage, $currentPage, ['path' => LengthAwarePaginator::resolveCurrentPath()]);
        return view('livewire.clientes.index', [
            'clientes' => $clientes,
        ]);
    }

    public function init()
    {
        if (user()->cannot('viewAnyCliente', [Cliente::class])) {
            $this->dispatch('show-toast', __('site.common.client_no_permissions'), 'danger');
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

        $clientes = Cache::remember(
            'clientes|idx|' . $this->filter,
            now()->addMinutes(5),
            // Cache 5 min (single-server): evita re-desencriptar toda tb_clientes en cada
            // tecla, orden o pagina. El cifrado se mantiene intacto (decision del proyecto).
            fn() => $query->where('es_cliente', 1)->get()->map->only(['id', 'logo', 'nombre_comercial', 'rfc', 'razon_social', 'telefono', 'deleted_at'])->toArray()
        );
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
            case 'Estado':
                if ($this->order == 'asc')
                    $records_final = $records_final->sortBy('deleted_at', SORT_REGULAR)->values();
                else
                    $records_final = $records_final->sortByDesc('deleted_at', SORT_REGULAR)->values();
                break;
        }

        return $records_final;
    }

    public function gestionarSuscripcion($id)
    {
        $cliente = Cliente::with('direccion_fiscal')->find($id);
        if (!$cliente->es_cliente) {
            $this->dispatch('show-toast', __('site.clients.restore.client_not_found'), 'danger');
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
        ]);

        if ($validator->fails()) {
            $messages = Arr::map(Arr::flatten($validator->messages()->messages()), function ($value) {
                return [
                    'type' => 'danger',
                    'text' => $value
                ];
            });
            $this->dispatch('openModal', component: 'modal-toast', arguments: ['messages' => $messages]);
            return;
        }

        return redirect()->route('admin.suscripciones.save', ['clienteId' => $id]);
    }

    public function changeSort($sort)
    {
        $this->order = !$this->order || $this->sort != $sort ? 'asc' : ($this->order == 'asc' ? 'desc' : '');
        $this->sort = !$this->order ? '' : $sort;
    }
}
