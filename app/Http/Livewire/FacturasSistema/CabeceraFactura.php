<?php

namespace App\Http\Livewire\FacturasSistema;

use App\Models\CabeceraFactura as ModelsCabeceraFactura;
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
    public $nombre_comercial;
    public $razon_social;
    public $rfc;
    public $correo;
    public $telefono;
    public $regimen_fiscal_id;
    public $portal_pac;
    public $usuario_integrador_sat;

    public $direccion = [
        'calle' => '',
        'no_exterior' => '',
        'no_interior' => '',
        'codigo_postal' => '',
        'colonia' => '',
        'referencia' => '',
        'estado_id' => '',
        'localidad_id' => '',
        'municipio_id' => '',
    ];
    public $regimenesFiscales = [];
    public $estados = [];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $owner = Cliente::where('es_propietario', 1)->first();

        $this->nombre_comercial = $owner && $owner->nombre_comercial ? Crypt::decrypt($owner->nombre_comercial) : '';
        $this->razon_social = $owner && $owner->razon_social ? Crypt::decrypt($owner->razon_social) : '';
        $this->rfc = $owner ? $owner->rfc : '';
        $this->correo = $owner && $owner->correo ? Crypt::decrypt($owner->correo) : '';
        $this->telefono = $owner && $owner->telefono ? Crypt::decrypt($owner->telefono) : '';
        $this->portal_pac = $owner ? $owner->portal_pac : '';
        $this->usuario_integrador_sat = $owner ? $owner->usuario_integrador_sat : '';
        $this->regimen_fiscal_id = $owner ? $owner->regimen_fiscal_id : '';
        if ($owner) {
            $this->direccion = Arr::except($owner->direccion_fiscal->toArray(), ['created_at', 'updated_at', 'direccion_formateada']);
        }

        $this->regimenesFiscales = RegimenFiscal::orderBy('codigo')->get()->map->only(['value', 'label']);
        $this->estados = get_estados_mexico();
    }

    public function hydrate()
    {
        $this->dispatchBrowserEvent('reApplySelect2');
        $this->init();
    }

    public function render()
    {
        return view('livewire.facturas-sistema.cabecera-factura');
    }

    public function init()
    {
        if (user()->cannot('setCabeceraFacturaFacturaSistema', [Factura::class])) {
            $this->emit('show-toast', __('site.common.client_no_permissions'), 'danger');
            return redirect()->to('/');
        }
        if ($this->direccion['estado_id']) {
            $estado = Estado::find($this->direccion['estado_id']);
            $this->dispatchBrowserEvent("set-data-direccion-estado_id", ['data' => [$estado->only(['id', 'text'])], 'term' => '', 'value' => $estado->id]);
        }
        if ($this->direccion['localidad_id']) {
            $localidad = Localidad::find($this->direccion['localidad_id']);
            $this->dispatchBrowserEvent("set-data-direccion-localidad_id", ['data' => [$localidad->only(['id', 'text'])], 'term' => '', 'value' => $localidad->id]);
        }
        if ($this->direccion['localidad_id']) {
            $municipio = Municipio::find($this->direccion['localidad_id']);
            $this->dispatchBrowserEvent("set-data-direccion-municipio_id", ['data' => [$municipio->only(['id', 'text'])], 'term' => '', 'value' => $municipio->id]);
        }
    }

    public function rulesDatosGenerales()
    {
        return [
            "nombre_comercial" => ['required'],
            "razon_social" => ['required'],
            "rfc" => ['required'],
            "correo" => ['required'],
            "telefono" => ['nullable'],
            "regimen_fiscal_id" => ['nullable'],
            "portal_pac" => ['required'],
            "usuario_integrador_sat" => ['required']
        ];
    }
    public function rulesDireccion()
    {
        return [
            "direccion.codigo_postal" => ['required'],
            "direccion.calle" => 'nullable',
            "direccion.no_exterior" => 'nullable',
            "direccion.no_interior" => 'nullable',
            "direccion.colonia" => 'nullable',
            "direccion.localidad_id" => 'nullable',
            "direccion.municipio_id" => 'nullable',
            "direccion.estado_id" => 'nullable',
            "direccion.referencia" => 'nullable'
        ];
    }

    public function messagesDatosGenerales()
    {
        return [
            "nombre_comercial.required" => 'Campo requerido',
            "razon_social.required" => 'Campo requerido',
            "rfc.required" => 'Campo requerido',
            "correo.required" => 'Campo requerido',
            "portal_pac.required" => 'Campo requerido',
            "usuario_integrador_sat.required" => 'Campo requerido',
        ];
    }
    public function messagesDireccion()
    {
        return [
            "direccion.codigo_postal.required" => 'Campo requerido.'
        ];
    }

    public function saveDatosGenerales()
    {
        $data = $this->validate(
            $this->rulesDatosGenerales(),
            // $this->messagesDatosGenerales()
        );
        $nombre_comercial = $data['nombre_comercial'] ? Crypt::encrypt($data['nombre_comercial']) : '';
        $razon_social = $data['razon_social'] ? Crypt::encrypt($data['razon_social']) : '';
        $telefono = $data['telefono'] ? Crypt::encrypt($data['telefono']) : '';
        $correo = $data['correo'] ? Crypt::encrypt($data['correo']) : '';

        $data['nombre_comercial'] = $nombre_comercial;
        $data['razon_social'] = $razon_social;
        $data['telefono'] = $telefono;
        $data['correo'] = $correo;

        $owner = Cliente::updateOrCreate([
            'es_propietario' => 1
        ], $data);



        activity(__('site.invoice_header.log_general_data_system_name'))
            ->on($owner)
            ->event('updated')
            ->withProperties(Cliente::parseData($data))
            ->log(__('site.invoice_header.log_general_data_system_detail'));

        $this->emit('show-toast', __('site.invoice_header.general_data_saved'));
        //        $this->emit('$refresh');
    }

    public function saveDireccion()
    {
        $data = $this->validate(
            $this->rulesDireccion(),
            // $this->messagesDireccion()
        );

        $owner = Cliente::where('es_propietario', 1)->first();

        if (!$owner) {
            $this->emit('show-toast', __('site.invoice_header.save_general_data_first'), 'danger');
            return;
        }

        if ($owner->direccion_fiscal_id)
            $owner->direccion_fiscal()->update($data['direccion']);
        else {
            $dir = $owner->direccion_fiscal()->create($data['direccion']);
            $owner->direccion_fiscal_id = $dir->id;
            $owner->save();
        }

        activity(__('site.invoice_header.log_address_system_name'))
            ->on($owner)
            ->event('updated')
            ->withProperties(Cliente::parseData($data['direccion']))
            ->log(__('site.invoice_header.log_address_system_detail'));

        $this->emit('show-toast', __('site.invoice_header.address_saved'));
        //        $this->emit('$refresh');
    }
}
