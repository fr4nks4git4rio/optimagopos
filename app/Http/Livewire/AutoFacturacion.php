<?php

namespace App\Http\Livewire;

use App\Models\Cfdi;
use App\Models\Cliente;
use App\Models\Direccion;
use App\Models\Estado;
use App\Models\Factura;
use App\Models\Facturador;
use App\Models\Localidad;
use App\Models\Municipio;
use App\Models\RegimenFiscal;
use App\Models\Sucursal;
use App\Models\Terminal;
use App\Models\Ticket;
use App\Models\TipoRelacionFactura;
use App\Rules\RfcRule;
use App\Rules\RuleUnique;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class AutoFacturacion extends Component
{
    public $codigo;
    public $ticket;
    public $suc;
    public $rfc;

    public $terminal = null;
    public $sucursales = [];
    public $regimenesFiscales = [];

    public $registrarComensalClass = '';
    public $ticketImageClass = '';
    public $alertaRegistrarComensal = '';
    public $rfc_exists = false;

    public $comensal = [
        'id' => null,
        'rfc' => '',
        'nombre_comercial' => '',
        'razon_social' => '',
        'correo' => '',
        'telefono' => '',
        'regimen_fiscal_id' => '',
        'direccion_fiscal' => [
            'calle' => '',
            'no_exterior' => '',
            'no_interior' => '',
            'codigo_postal' => '',
            'colonia' => '',
            'estado_id' => '',
            'localidad_id' => '',
            'municipio_id' => '',
            'referencia' => ''
        ]
    ];

    protected $queryString = ['codigo', 'ticket'];

    protected $listeners = ['$refresh'];

    public function mount()
    {
        $this->regimenesFiscales = RegimenFiscal::orderBy('codigo')->get()->map->only('label', 'value');
        $this->sucursales = DB::table('tb_sucursales as s')
            ->select('s.id as value', 's.nombre_comercial as label')
            ->leftJoin('tb_clientes as c', 'c.id', '=', 's.cliente_id')
            ->whereNull('s.deleted_at')
            ->whereNull('c.deleted_at')
            ->get()->map(function ($element) {
                $element->label = Crypt::decrypt($element->label);
                return (array)$element;
            })->toArray();
        if ($this->codigo) {
            $this->terminal = Terminal::findByIdentificador($this->codigo);
            if ($this->terminal) {
                $this->suc = $this->terminal->sucursal_id;
            }
        }
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

    public function updatedSuc($value)
    {
        $this->rfc_exists = false;
        if ($value) {
            if ($this->rfc) {
                $this->checkRfc();
            }
        }
    }

    public function updatedRfc($value)
    {
        $this->rfc_exists = false;
        if ($value) {
            $this->checkRfc($value);
        }
    }

    public function updatedCodigo($value)
    {
        $this->terminal = null;
        if ($value) {
            $this->terminal = Terminal::findByIdentificador($value);
        }
    }

    public function getSucursalProperty()
    {
        return $this->suc ? Sucursal::find($this->suc) : null;
    }

    public function getLogoProperty()
    {
        return $this->sucursal ? $this->sucursal->logo_uri : '';
    }
    public function getTelefonoProperty()
    {
        return $this->sucursal && $this->sucursal->telefono ? Crypt::decrypt($this->sucursal->telefono) : '';
    }

    public function getCorreoProperty()
    {
        return $this->sucursal && $this->sucursal->correo ? Crypt::decrypt($this->sucursal->correo) : '';
    }

    public function getRazonSocialComensalProperty()
    {
        if ($this->rfc_exists) {
            $razon_social = DB::table('tb_clientes')->select('razon_social')
                ->where('rfc', $this->rfc)
                ->first();
            if (!$razon_social)
                return '';
            $razon_social = $razon_social->razon_social;
            return Crypt::decrypt($razon_social);
        }
        return '';
    }

    public function getCodigoPostalComensalProperty()
    {
        if ($this->rfc_exists) {
            $codigo_postal = DB::table('tb_clientes as c')
                ->select('d.codigo_postal')
                ->leftJoin('tb_direcciones as d', 'd.id', '=', 'c.direccion_fiscal_id')
                ->where('c.rfc', $this->rfc)->first();
            if (!$codigo_postal)
                return '';
            $codigo_postal = $codigo_postal->codigo_postal;
            return $codigo_postal;
        }
        return '';
    }

    public function render()
    {
        return view('livewire.auto-facturacion');
    }

    public function init()
    {
        if ($this->comensal['direccion_fiscal']['estado_id']) {
            $estado = Estado::find($this->comensal['direccion_fiscal']['estado_id']);
            $this->dispatchBrowserEvent("set-data-comensal-direccion_fiscal-estado_id", ['data' => [$estado->only('id', 'text')], 'term' => '', 'value' => $estado->id]);
        }
        if ($this->comensal['direccion_fiscal']['localidad_id']) {
            $localidad = Localidad::find($this->comensal['direccion_fiscal']['localidad_id']);
            $this->dispatchBrowserEvent("set-data-comensal-direccion_fiscal-localidad_id", ['data' => [$localidad->only('id', 'text')], 'term' => '', 'value' => $localidad->id]);
        }
        if ($this->comensal['direccion_fiscal']['municipio_id']) {
            $municipio = Municipio::find($this->comensal['direccion_fiscal']['municipio_id']);
            $this->dispatchBrowserEvent("set-data-comensal-direccion_fiscal-municipio_id", ['data' => [$municipio->only('id', 'text')], 'term' => '', 'value' => $municipio->id]);
        }
    }

    public function guardarComensal()
    {
        $collection = DB::table('tb_clientes')
            ->select('id', 'nombre_comercial', 'razon_social', 'rfc', DB::raw('0 as decrypted'))
            ->get();
        $collection->map(function ($cliente) {
            $cliente = Cliente::decryptInfo($cliente);
        });
        $tipo_persona = 'ambas';
        if ($this->comensal['regimen_fiscal_id']) {
            if ($this->comensal['regimen_fiscal_id'] == 9)
                $tipo_persona = 'persona_fisica';
            else
                $tipo_persona = 'persona_moral';
        }
        $data = $this->validate([
            'comensal.id' => 'nullable',
            'comensal.rfc' => ['required', new RuleUnique($collection, $this->comensal['id']), new RfcRule($tipo_persona)],
            'comensal.nombre_comercial' => ['required', new RuleUnique($collection, $this->comensal['id'])],
            'comensal.razon_social' => ['required', new RuleUnique($collection, $this->comensal['id'])],
            'comensal.correo' => ['required'],
            'comensal.telefono' => ['nullable'],
            'comensal.regimen_fiscal_id' => ['nullable'],
            'comensal.direccion_fiscal.codigo_postal' => ['required'],
            'comensal.direccion_fiscal.calle' => 'nullable',
            'comensal.direccion_fiscal.no_exterior' => 'nullable',
            'comensal.direccion_fiscal.no_interior' => 'nullable',
            'comensal.direccion_fiscal.colonia' => 'nullable',
            'comensal.direccion_fiscal.localidad_id' => 'nullable',
            'comensal.direccion_fiscal.municipio_id' => 'nullable',
            'comensal.direccion_fiscal.estado_id' => 'nullable',
            'comensal.direccion_fiscal.referencia' => 'nullable'
        ], [
            'comensal.nombre_comercial.required' => 'Campo requerido',
            'comensal.razon_social.required' => 'Campo requerido',
            'comensal.rfc.required' => 'Campo requerido',
            'comensal.correo.required' => 'Campo requerido',
            'comensal.direccion_fiscal.codigo_postal.required' => 'Campo requerido.'
        ]);

        if (!$data['comensal']['id']) {
            $newComensal = Cliente::create([
                'rfc' => $data['comensal']['rfc'],
                'nombre_comercial' => Crypt::encrypt($data['comensal']['nombre_comercial']),
                'razon_social' => Crypt::encrypt($data['comensal']['razon_social']),
                'correo' => Crypt::encrypt($data['comensal']['correo']),
                'telefono' => $data['comensal']['telefono'] ? Crypt::encrypt($data['comensal']['telefono']) : '',
                'regimen_fiscal_id' => $data['comensal']['regimen_fiscal_id'],
                'es_comensal' => 1
            ]);
            $dir = Direccion::create([
                'codigo_postal' => $data['comensal']['direccion_fiscal']['codigo_postal'],
                'calle' => $data['comensal']['direccion_fiscal']['calle'],
                'no_exterior' => $data['comensal']['direccion_fiscal']['no_exterior'],
                'no_interior' => $data['comensal']['direccion_fiscal']['no_interior'],
                'colonia' => $data['comensal']['direccion_fiscal']['colonia'],
                'localidad_id' => $data['comensal']['direccion_fiscal']['localidad_id'] ? $data['comensal']['direccion_fiscal']['localidad_id'] : null,
                'municipio_id' => $data['comensal']['direccion_fiscal']['municipio_id'] ? $data['comensal']['direccion_fiscal']['municipio_id'] : null,
                'estado_id' => $data['comensal']['direccion_fiscal']['estado_id'] ? $data['comensal']['direccion_fiscal']['estado_id'] : null,
                'referencia' => $data['comensal']['direccion_fiscal']['referencia'],
            ]);
            $newComensal->direccion_fiscal_id = $dir->id;
            $newComensal->saveQuietly();
            activity("Dirección Fiscal de Cliente Creada")
                ->on($dir)
                ->event('created')
                ->withProperties(Direccion::parseData(Arr::except($dir->toArray(), ['updated_at'])))
                ->log('La Dirección Fiscal del Cliente con RFC: ' . $newComensal->rfc . ' ha sido creada.');

            $this->emit('show-toast', 'Datos guardados satisfactoriamente', 'success');
            $this->rfc = $newComensal->rfc;
            $this->rfc_exists = true;
            $this->registrarComensalClass = '';
        }
    }

    public function checkRfc($rfc = '')
    {
        $rfc = $rfc ?: $this->rfc;
        $this->validate([
            'suc' => ['required'],
            'rfc' => ['required', new RfcRule('ambas')]
        ], [
            'suc.required' => 'Campo requerido!',
            'rfc.required' => 'Campo requerido!',
        ]);
        $comensal = DB::table('tb_clientes as c')
            ->select('c.*')
            ->where('c.es_comensal', 1)
            ->where('rfc', $rfc)
            ->first();
        if (!$comensal) {
            $this->alertaRegistrarComensal = "Lo sentimos su RFC no se encuentra registrado en nuestra plataforma. Verifique que su RFC esta correctamente escrito, y de ser asi puede llenar sus datos en el formulario que se muestra.";
            $this->comensal['id'] = null;
            $this->comensal['rfc'] = Str::upper($rfc);
            $this->registrarComensalClass = 'show';
            $this->rfc = '';
        } else {
            $this->rfc_exists = true;
        }
    }

    public function facturar()
    {
        $data = $this->validate([
            'suc' => ['required', 'exists:tb_sucursales,id'],
            'codigo' => ['required', 'exists:tb_terminales,identificador'],
            'ticket' => ['required', 'exists:tb_tickets,id_transaccion'],
            'rfc' => ['required', 'exists:tb_clientes,rfc']
        ], [
            'suc.required' => 'Campo requerido!',
            'suc.exists' => 'Sucursal no encontrada!',
            'codigo.required' => 'Campo requerido!',
            'codigo.exists' => 'Código no encontrado!',
            'ticket.required' => 'Campo requerido!',
            'ticket.exists' => 'Ticket no encontrado!',
            'rfc.required' => 'Campo requerido!',
            'rfc.exists' => 'RFC no encontrado!'
        ]);

        if (
            DB::table('tb_tickets')
            ->where('id_transaccion', $data['ticket'])
            ->where('sucursal_id', $this->suc)->count() == 0
        ) {
            $this->addError('ticket', 'El Ticket no fue emitido por la Sucursal seleccionada!');
            return;
        }

        if (
            DB::table('tb_tickets')
            ->where('id_transaccion', $data['ticket'])
            ->where('terminal_id', $this->terminal->id)->count() == 0
        ) {
            $this->addError('ticket', 'El Ticket no se corresponde con el Código entrado!');
            return;
        }

        $ticket = Ticket::where('id_transaccion', $this->ticket)->where('terminal_id', $this->terminal->id)->first();

        if (!$ticket) {
            $this->emit('show-toast', 'Ticket no encontrado.', 'danger');
            return;
        }
        if ($ticket->factura()->exists() && $ticket->factura->estado == 'TIMBRADA') {
            $this->emit('openModal', 'modal-toast', ['messages' => [['type' => 'success', 'text' => 'El Ticket ya ha sido facturado previamente.']]]);
            return;
        }
        if ($ticket->factura()->exists()) {
            if ($ticket->factura->estado != 'CANCELADA') {
                $ticket->factura->factura_conceptos()->delete();
                $ticket->factura->delete();
            }
            $ticket->factura_id = null;
            $ticket->save();
        }

        $propietario = Sucursal::decryptInfo(Sucursal::find($this->suc));
        $cliente = Cliente::decryptInfo(Cliente::where('rfc', operator: $this->rfc)->first());

        $factura = Factura::create([
            'propietario_id' => $this->suc,
            'lugar_expedicion' => $propietario->codigo_postal,
            'cliente_id' => $cliente->id,
            'fecha_emision' => now(),
            'moneda' => 'MXN',
            'estado' => 'CAPTURADA',
            'modo_prueba_cfdi' => modo_facturacion() != 1,
            'porciento_iva' => system_iva(),
            'total' => $ticket->importe,
            'subtotal' => $ticket->importe / (1 + system_iva() / 100),
            'iva' => $ticket->importe - $ticket->importe / (1 + system_iva() / 100),
            'cantidad_letras' => convertir_numero_a_letras($ticket->importe, 'MXN'),
            'cfdi_id' => 3,
            'serie_id' => 1,
            'regimen_fiscal_id' => $cliente->regimen_fiscal_id,
            'metodo_pago_id' => 1,
            'forma_pago_id' => 1,
            'tipo_comprobante_id' => 1
        ]);

        $ticket->factura_id = $factura->id;
        $ticket->save();

        $this->redirect("/timbrar-auto-factura/$factura->id");
    }

    public function mostrarTicketMuestra()
    {
        $this->ticketImageClass = 'show';
    }
}
