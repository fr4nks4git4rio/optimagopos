<?php

namespace App\Http\Livewire;

use App\Models\RegimenFiscal;
use App\Models\Direccion;
use App\Models\Estado;
use App\Models\Localidad;
use App\Models\Municipio;
use App\Models\Cliente;
use App\Rules\RfcRule;
use App\Rules\RfcYRegimenCoherentesRule;
use App\Rules\RuleUnique;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class CabeceraFactura extends Component
{
    use WithFileUploads;

    public $owner;
    public $regimenesFiscales = [];
    public $estados = [];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->owner = Cliente::decryptInfo(get_owner()->toArray());
        $this->regimenesFiscales = RegimenFiscal::orderBy('codigo')->get()->map->only(['value', 'label']);
        $this->estados = get_estados_mexico();

        $direccion = Cliente::find($this->owner['id'])->direccion_fiscal;
        $this->owner['direccion'] = $direccion->toArray();
    }

    public function hydrate()
    {
        $this->dispatchBrowserEvent('reApplySelect2');
        $this->init();
    }

    public function updated()
    {
        $this->dispatchBrowserEvent('reApplySelect2');
        $this->init();
    }

    public function render()
    {
        //        $this->cuentasBancarias = $this->owner->cuentas_bancarias;
        return view('livewire.cabecera-factura');
    }

    public function init()
    {
        if ($this->owner['direccion']['estado_id']) {
            $estado = Estado::find($this->owner['direccion']['estado_id']);
            $this->dispatchBrowserEvent("set-data-owner-direccion-estado_id", ['data' => [$estado->only(['id', 'text'])], 'term' => '', 'value' => $estado->id]);
        }
        if ($this->owner['direccion']['localidad_id']) {
            $localidad = Localidad::find($this->owner['direccion']['localidad_id']);
            $this->dispatchBrowserEvent("set-data-owner-direccion-localidad_id", ['data' => [$localidad->only(['id', 'text'])], 'term' => '', 'value' => $localidad->id]);
        }
        if ($this->owner['direccion']['municipio_id']) {
            $municipio = Municipio::find($this->owner['direccion']['municipio_id']);
            $this->dispatchBrowserEvent("set-data-owner-direccion-municipio_id", ['data' => [$municipio->only(['id', 'text'])], 'term' => '', 'value' => $municipio->id]);
        }
    }

    public function rules()
    {
        $collection = DB::table('tb_clientes')
            ->select('id', 'nombre_comercial', 'razon_social', 'rfc', DB::raw('0 as decrypted'))
            ->get();
        $collection->map(function ($cliente) {
            $cliente = Cliente::decryptInfo($cliente);
        });
        return [
            'owner.nombre_comercial' => ['required', new RuleUnique($collection, $this->owner['id'])],
            'owner.razon_social' => ['required', new RuleUnique($collection, $this->owner['id'])],
            'owner.rfc' => ['required', new RuleUnique($collection, $this->owner['id']), new RfcRule('ambas')],
            'owner.correo' => ['required'],
            'owner.telefono' => ['nullable'],
            'owner.regimen_fiscal_id' => ['nullable', new RfcYRegimenCoherentesRule($this->owner->rfc)],
            'owner.portal_pac' => ['required'],
            'owner.usuario_integrador_sat' => ['required'],
            'owner.direccion.codigo_postal' => ['required'],
            'owner.direccion.calle' => 'nullable',
            'owner.direccion.no_exterior' => 'nullable',
            'owner.direccion.no_interior' => 'nullable',
            'owner.direccion.colonia' => 'nullable',
            'owner.direccion.localidad_id' => 'nullable',
            'owner.direccion.municipio_id' => 'nullable',
            'owner.direccion.estado_id' => 'nullable',
            'owner.direccion.referencia' => 'nullable'
        ];
    }

    public function messages()
    {
        return [
            'owner.nombre_comercial.required' => 'Campo requerido',
            'owner.razon_social.required' => 'Campo requerido',
            'owner.rfc.required' => 'Campo requerido',
            'owner.correo.required' => 'Campo requerido',
            'owner.portal_pac.required' => 'Campo requerido',
            'owner.usuario_integrador_sat.required' => 'Campo requerido',
            'owner.direccion.codigo_postal.required' => 'Campo requerido.'
        ];
    }

    public function saveDatosGenerales()
    {
        $data = $this->validate($this->rules(), $this->messages());
        $nombre_comercial = $data['owner']['nombre_comercial'] ? Crypt::encrypt($data['owner']['nombre_comercial']) : '';
        $razon_social = $data['owner']['razon_social'] ? Crypt::encrypt($data['owner']['razon_social']) : '';
        $telefono = $data['owner']['telefono'] ? Crypt::encrypt($data['owner']['telefono']) : '';
        $correo = $data['owner']['correo'] ? Crypt::encrypt($data['owner']['correo']) : '';
        $cliente = Cliente::find($this->owner['id']);
        $cliente->fill([
            'nombre_comercial' => $nombre_comercial,
            'razon_social' => $razon_social,
            'rfc' => $data['owner']['rfc'],
            'regimen_fiscal_id' => $data['owner']['regimen_fiscal_id'],
            'correo' => $correo,
            'telefono' => $telefono,
            'portal_pac' => $data['owner']['portal_pac'],
            'usuario_integrador_sat' => $data['owner']['usuario_integrador_sat'],
        ])->save();

        $direccion = $cliente->direccion_fiscal;
        $direccion->calle = $data['owner']['direccion']['calle'];
        $direccion->no_exterior = $data['owner']['direccion']['no_exterior'];
        $direccion->no_interior = $data['owner']['direccion']['no_interior'];
        $direccion->codigo_postal = $data['owner']['direccion']['codigo_postal'];
        $direccion->colonia = $data['owner']['direccion']['colonia'];
        $direccion->localidad_id = $data['owner']['direccion']['localidad_id'];
        $direccion->municipio_id = $data['owner']['direccion']['municipio_id'];
        $direccion->estado_id = $data['owner']['direccion']['estado_id'];
        $direccion->referencia = $data['owner']['direccion']['referencia'];

        if ($direccion->id) {
            if (count($direccion->getDirty()) > 0) {
                $attributes = Arr::except($direccion->getDirty(), ['created_at', 'updated_at']);
                activity('Dirección Fiscal de Cliente Actualizada')
                    ->on($direccion)
                    ->event('updated')
                    ->withProperty('attributes', Direccion::parseData($attributes))
                    ->withProperty('old', Direccion::parseData(Arr::only($direccion->getOriginal(), array_keys($attributes))))
                    ->log('La Dirección Fiscal del Cliente con RFC: ' . $this->owner['rfc'] . ' ha sido actualizada.');
            }
        } else {
            activity("Dirección Fiscal de Cliente Creada")
                ->on($direccion)
                ->event('created')
                ->withProperties(Direccion::parseData(Arr::except($direccion->toArray(), ['updated_at'])))
                ->log('La Dirección Fiscal del Cliente con RFC: ' . $this->owner['rfc'] . ' ha sido creada.');
        }
        $direccion->save();

        activity("Cabecera de Factura de Cliente Actualizada")
            ->on($cliente)
            ->event('updated')
            ->withProperties(Cliente::parseData(Arr::except($cliente->toArray(), ['created_at', 'updated_at', 'deleted_at'])))
            ->log('Los datos de la Cabecera de Factura del Cliente con RFC: ' . $this->owner['rfc'] . ' ha sido actualizada.');

        $this->emit('show-toast', 'Datos Generales guardados.');
        //        $this->emit('$refresh');
    }
}
