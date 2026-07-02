<?php

namespace App\Http\Livewire;

use App\Models\RegimenFiscal;
use App\Models\Direccion;
use App\Models\Estado;
use App\Models\Localidad;
use App\Models\Municipio;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Moneda;
use App\Models\Sucursal;
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
    public $sucursales = [];
    public $regimenesFiscales = [];
    public $estados = [];
    public $monedas = [];
    public $formasPagoOptions = [];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $owner = get_owner();
        $this->regimenesFiscales = RegimenFiscal::orderBy('codigo')->get()->map->only(['value', 'label']);
        $this->estados = get_estados_mexico();
        $this->monedas = Moneda::all()->map->only(['value', 'label'])->toArray();
        $this->formasPagoOptions = DB::table('tb_forma_pagos')
            ->select('id as value', DB::raw("CONCAT_WS(' | ', codigo, descripcion) as label"))
            ->get()
            ->map(function ($value, $key) {
                return (array)$value;
            })->toArray();

        foreach ($owner->sucursales as $sucursal) {
            $sucursalArr = Sucursal::decryptInfo(Arr::only($sucursal->toArray(), ['id', 'nombre_comercial', 'razon_social', 'rfc', 'correo', 'telefono', 'portal_pac', 'usuario_integrador_sat', 'regimen_fiscal_id', 'moneda_facturacion_id']));
            $sucursalArr['direccion'] = Arr::except($sucursal->direccion_fiscal->toArray(), ['created_at', 'updated_at', 'direccion_formateada']);
            $sucursalArr['formas_pago'] = [];
            $this->sucursales[] = $sucursalArr;
        }

        $this->loadFormasPago();
    }

    public function hydrate()
    {
        $this->dispatchBrowserEvent('reApplySelect2');
        $this->init();
    }

    public function updated($field, $value)
    {
        if (preg_match('/^sucursales\.\d+\.formas_pago\.\d+\.forma_pago_id$/', $field)) {
            preg_match('/^sucursales\.(\d+)\.formas_pago\.(\d+)\.forma_pago_id$/', $field, $matches);
            $sucursalIndex   = $matches[1]; // ej: 0
            $formaPagoIndex  = $matches[2]; // ej: 4

            DB::table('tb_sucursal_forma_pagos as sfp')
                ->where('id', $this->sucursales[$sucursalIndex]['formas_pago'][$formaPagoIndex]['id'])
                ->update(['forma_pago_id' => $value ?? null]);

            $this->emit('show-toast', 'Forma de pago actualizada', 'success');
        }
        $this->dispatchBrowserEvent('reApplySelect2');
        $this->init();
    }

    public function render()
    {
        return view('livewire.cabecera-factura');
    }

    public function init()
    {
        if (user()->cannot('setCabeceraFactura', [Factura::class])) {
            $this->emit('show-toast', 'No tiene permisos para realizar estar acción.', 'danger');
            return redirect()->to('/');
        }
        foreach ($this->sucursales as $index => $sucursal) {
            if ($sucursal['direccion']['estado_id']) {
                $estado = Estado::find($sucursal['direccion']['estado_id']);
                $this->dispatchBrowserEvent("set-data-sucursales-$index-direccion-estado_id", ['data' => [$estado->only(['id', 'text'])], 'term' => '', 'value' => $estado->id]);
            }
            if ($sucursal['direccion']['localidad_id']) {
                $localidad = Localidad::find($sucursal['direccion']['localidad_id']);
                $this->dispatchBrowserEvent("set-data-sucursales-$index-direccion-localidad_id", ['data' => [$localidad->only(['id', 'text'])], 'term' => '', 'value' => $localidad->id]);
            }
            if ($sucursal['direccion']['municipio_id']) {
                $municipio = Municipio::find($sucursal['direccion']['municipio_id']);
                $this->dispatchBrowserEvent("set-data-sucursales-$index-direccion-municipio_id", ['data' => [$municipio->only(['id', 'text'])], 'term' => '', 'value' => $municipio->id]);
            }
        }
    }

    public function loadFormasPago($index = null)
    {
        if ($index) {
            $this->sucursales[$index]['formas_pago'] = DB::table('tb_sucursal_forma_pagos as sfp')
                ->select(
                    'sfp.id',
                    'sfp.nombre',
                    'moneda.acronimo as moneda',
                    'sfp.moneda_id',
                    'sfp.forma_pago_id',
                    DB::raw("CONCAT_WS(' | ', fp.codigo, fp.descripcion) as forma_pago_sat"),
                    'sfp.deleted_at'
                )
                ->leftJoin('tb_forma_pagos as fp', 'fp.id', '=', 'sfp.forma_pago_id')
                ->leftJoin('tb_monedas as moneda', 'moneda.id', '=', 'sfp.moneda_id')
                ->where('sucursal_id', $this->sucursales[$index]['id'])
                ->get()->map(function ($value, $key) {
                    return (array)$value;
                })->toArray();
        } else {
            foreach ($this->sucursales as $i => $sucursal) {
                $this->sucursales[$i]['formas_pago'] = DB::table('tb_sucursal_forma_pagos as sfp')
                    ->select(
                        'sfp.id',
                        'sfp.nombre',
                        'moneda.acronimo as moneda',
                        'sfp.moneda_id',
                        'sfp.forma_pago_id',
                        DB::raw("CONCAT_WS(' | ', fp.codigo, fp.descripcion) as forma_pago_sat"),
                        'sfp.deleted_at'
                    )
                    ->leftJoin('tb_forma_pagos as fp', 'fp.id', '=', 'sfp.forma_pago_id')
                    ->leftJoin('tb_monedas as moneda', 'moneda.id', '=', 'sfp.moneda_id')
                    ->where('sucursal_id', $sucursal['id'])
                    ->get()->map(function ($value, $key) {
                        return (array)$value;
                    })->toArray();
            }
        }
    }

    public function rulesDatosGenerales($index = 0)
    {
        $collection = DB::table('tb_sucursales')
            ->select('id', 'nombre_comercial', 'razon_social', 'rfc', DB::raw('0 as decrypted'))
            ->get();
        $collection->map(function ($sucursal) {
            $sucursal = Sucursal::decryptInfo($sucursal);
        });
        return [
            "sucursales.$index.id" => ['required'],
            "sucursales.$index.nombre_comercial" => ['required', new RuleUnique($collection, $this->sucursales[$index]['id'])],
            "sucursales.$index.razon_social" => ['required', new RuleUnique($collection, $this->sucursales[$index]['id'])],
            "sucursales.$index.rfc" => ['required', new RuleUnique($collection, $this->sucursales[$index]['id']), new RfcRule('ambas')],
            "sucursales.$index.correo" => ['required'],
            "sucursales.$index.telefono" => ['nullable'],
            "sucursales.$index.regimen_fiscal_id" => ['nullable', new RfcYRegimenCoherentesRule($this->sucursales[$index]['rfc'])],
            "sucursales.$index.moneda_facturacion_id" => ['required', 'exists:tb_monedas,id'],
            "sucursales.$index.portal_pac" => ['required'],
            "sucursales.$index.usuario_integrador_sat" => ['required']
        ];
    }
    public function rulesDireccion($index = 0)
    {
        $collection = DB::table('tb_sucursales')
            ->select('id', 'nombre_comercial', 'razon_social', 'rfc', DB::raw('0 as decrypted'))
            ->get();
        $collection->map(function ($sucursal) {
            $sucursal = Sucursal::decryptInfo($sucursal);
        });
        return [
            "sucursales.$index.id" => ['required'],
            "sucursales.$index.direccion.codigo_postal" => ['required'],
            "sucursales.$index.direccion.calle" => 'nullable',
            "sucursales.$index.direccion.no_exterior" => 'nullable',
            "sucursales.$index.direccion.no_interior" => 'nullable',
            "sucursales.$index.direccion.colonia" => 'nullable',
            "sucursales.$index.direccion.localidad_id" => 'nullable',
            "sucursales.$index.direccion.municipio_id" => 'nullable',
            "sucursales.$index.direccion.estado_id" => 'nullable',
            "sucursales.$index.direccion.referencia" => 'nullable'
        ];
    }

    public function messagesDatosGenerales($index)
    {
        return [
            "sucursales.$index.nombre_comercial.required" => 'Campo requerido',
            "sucursales.$index.razon_social.required" => 'Campo requerido',
            "sucursales.$index.rfc.required" => 'Campo requerido',
            "sucursales.$index.correo.required" => 'Campo requerido',
            "sucursales.$index.portal_pac.required" => 'Campo requerido',
            "sucursales.$index.usuario_integrador_sat.required" => 'Campo requerido',
            "sucursales.$index.moneda_facturacion_id.required" => 'Campo requerido',
            "sucursales.$index.moneda_facturacion_id.exists" => 'Moneda no encontrada',
        ];
    }
    public function messagesDireccion($index)
    {
        return [
            "sucursales.$index.direccion.codigo_postal.required" => 'Campo requerido.'
        ];
    }

    public function saveDatosGenerales($index)
    {
        $data = $this->validate(
            $this->rulesDatosGenerales($index),
            // $this->messagesDatosGenerales($index)
        );
        $dataSucursal = $data['sucursales'][$index];
        $nombre_comercial = $dataSucursal['nombre_comercial'] ? Crypt::encrypt($dataSucursal['nombre_comercial']) : '';
        $razon_social = $dataSucursal['razon_social'] ? Crypt::encrypt($dataSucursal['razon_social']) : '';
        $telefono = $dataSucursal['telefono'] ? Crypt::encrypt($dataSucursal['telefono']) : '';
        $correo = $dataSucursal['correo'] ? Crypt::encrypt($dataSucursal['correo']) : '';
        $sucursal = Sucursal::find($dataSucursal['id']);
        $sucursal->fill([
            'nombre_comercial' => $nombre_comercial,
            'razon_social' => $razon_social,
            'rfc' => $dataSucursal['rfc'],
            'regimen_fiscal_id' => $dataSucursal['regimen_fiscal_id'],
            'correo' => $correo,
            'telefono' => $telefono,
            'portal_pac' => $dataSucursal['portal_pac'],
            'usuario_integrador_sat' => $dataSucursal['usuario_integrador_sat'],
            'moneda_facturacion_id' => $dataSucursal['moneda_facturacion_id']
        ])->save();

        activity("Cabecera de Factura de Sucursal Actualizada")
            ->on($sucursal)
            ->event('updated')
            ->withProperties(Sucursal::parseData(Arr::except($sucursal->toArray(), ['created_at', 'updated_at', 'deleted_at'])))
            ->log('Los datos de la Cabecera de Factura de la Sucursal con RFC: ' . $sucursal->rfc . ' ha sido actualizada.');

        $this->emit('show-toast', 'Datos Generales guardados.');
        //        $this->emit('$refresh');
    }

    public function saveDireccion($index)
    {
        $data = $this->validate(
            $this->rulesDireccion($index),
            // $this->messagesDireccion($index)
        );
        $dataSucursal = $data['sucursales'][$index];
        $sucursal = Sucursal::find($dataSucursal['id']);

        $direccion = $sucursal->direccion_fiscal;
        $direccion->calle = $dataSucursal['direccion']['calle'];
        $direccion->no_exterior = $dataSucursal['direccion']['no_exterior'];
        $direccion->no_interior = $dataSucursal['direccion']['no_interior'];
        $direccion->codigo_postal = $dataSucursal['direccion']['codigo_postal'];
        $direccion->colonia = $dataSucursal['direccion']['colonia'];
        $direccion->localidad_id = $dataSucursal['direccion']['localidad_id'];
        $direccion->municipio_id = $dataSucursal['direccion']['municipio_id'];
        $direccion->estado_id = $dataSucursal['direccion']['estado_id'];
        $direccion->referencia = $dataSucursal['direccion']['referencia'];

        if ($direccion->id) {
            if (count($direccion->getDirty()) > 0) {
                $attributes = Arr::except($direccion->getDirty(), ['created_at', 'updated_at']);
                activity('Dirección Fiscal de Sucursal Actualizada')
                    ->on($direccion)
                    ->event('updated')
                    ->withProperty('attributes', Direccion::parseData($attributes))
                    ->withProperty('old', Direccion::parseData(Arr::only($direccion->getOriginal(), array_keys($attributes))))
                    ->log('La Dirección Fiscal del Sucursal con RFC: ' . $sucursal->rfc . ' ha sido actualizada.');
            }
        } else {
            activity("Dirección Fiscal de Sucursal Creada")
                ->on($direccion)
                ->event('created')
                ->withProperties(Direccion::parseData(Arr::except($direccion->toArray(), ['updated_at'])))
                ->log('La Dirección Fiscal de la Sucursal con RFC: ' . $sucursal->rfc . ' ha sido creada.');
        }
        $direccion->save();

        $this->emit('show-toast', 'Dirección guardada.');
        //        $this->emit('$refresh');
    }
}
