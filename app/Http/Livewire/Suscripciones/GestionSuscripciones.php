<?php

namespace App\Http\Livewire\Suscripciones;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Cliente;
use App\Models\Config;
use App\Models\Modulo;
use App\Models\Paquete;
use App\Models\Sucursal;
use App\Models\Suscripcion;
use App\Models\Terminal;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class GestionSuscripciones extends Component
{
    public Suscripcion $suscripcion;
    public $clienteId = '';
    public $cliente_id = '';
    public $paquete_id = '';
    public $cant_sucursales = 1;
    public $cant_terminales = 1;
    public $cant_usuarios = 1;
    public $fecha_inicio_operaciones;
    public $fecha_inicio_pagos;
    public $periodicidad_pagos = 'MENSUAL';
    public $estado = 'PENDIENTE';
    public $modulos = [];

    // Propiedades calculadas en tiempo real para la vista
    public $precio_paquete = 0.00;
    public $precio_extra = 0.00;
    public $precio_total = 0.00;
    public $descuento = 0.00;
    public $total = 0.00;

    // Objeto para almacenar la configuración global de la base de datos
    public $globalSettings;

    public $sucursales = [];
    public $sucursalesDisponibles = [];
    public $terminales = [];
    public $terminalesDisponibles = [];
    public $usuarios = [];
    public $usuariosDisponibles = [];
    protected $queryString = [
        'clienteId' => ['except' => '']
    ];
    protected $listeners = ['$refresh', 'sucursal-created' => 'AddSucursal', 'terminal-created' => 'AddTerminal', 'usuario-created' => 'AddUsuario'];

    protected function rules()
    {
        return [
            'cant_sucursales' => 'required|integer|min:1',
            'cant_terminales' => 'required|integer|min:1',
            'cant_usuarios' => 'required|integer|min:1',
            'modulos' => 'nullable|array',
            'modulos.*' => 'exists:tb_modulos,id',
            'cliente_id' => 'required|exists:tb_clientes,id',
            'paquete_id' => 'nullable',
            'fecha_inicio_operaciones' => 'required|date',
            'fecha_inicio_pagos' => 'required|date',
            'periodicidad_pagos' => 'required|in:MENSUAL,BIMESTRAL,TRIMESTRAL,SEMESTRAL,ANUAL',
            'precio_paquete' => 'required|numeric',
            'precio_extra' => 'required|numeric',
            'precio_total' => 'required|numeric',
            'descuento' => 'nullable|numeric|min:0|max:' . ($this->precio_paquete + $this->precio_extra),
            'total' => 'required|numeric',
            'sucursales' => 'nullable|array',
            'terminales' => 'nullable|array',
            'usuarios' => 'nullable|array'
        ];
    }

    // protected $messages = [
    //     'cliente_id.required' => 'Seleccione el Cliente.',
    //     'cliente_id.exists' => 'Cliente no encontrado.',
    //     'fecha_inicio_operaciones.required' => 'La fecha de inicio de operaciones es obligatoria.',
    //     'fecha_inicio_pagos.required' => 'La fecha de próximo cobro es obligatoria.',
    //     'periodicidad_pagos.in' => 'Período no encontrado'
    // ];

    public function mount($suscripcionId = null)
    {

        if ($this->clienteId) {
            $this->cliente_id = $this->clienteId;
            $this->clienteId = '';
        }
        // Traer las configuraciones globales de precios por excedentes
        $this->globalSettings = Config::all()->pluck('valor', 'llave')->toArray();

        if ($suscripcionId) {
            $this->suscripcion = Suscripcion::find($suscripcionId);
            $this->paquete_id = $this->suscripcion->paquete_id ?? '';
            $this->cliente_id = $this->suscripcion->cliente_id;
            $this->cant_sucursales = $this->suscripcion->cant_sucursales;
            $this->cant_terminales = $this->suscripcion->cant_terminales;
            $this->cant_usuarios = $this->suscripcion->cant_usuarios;
            $this->fecha_inicio_operaciones = $this->suscripcion->fecha_inicio_operaciones ? $this->suscripcion->fecha_inicio_operaciones->format('Y-m-d') : today()->format('Y-m-d');
            $this->fecha_inicio_pagos = $this->suscripcion->fecha_inicio_pagos ? $this->suscripcion->fecha_inicio_pagos->format('Y-m-d') : today()->format('Y-m-d');
            $this->periodicidad_pagos = $sub->periodicidad_pagos ?? 'MENSUAL';
            $this->estado = $sub->estado ?? 'PENDIENTE';
            $this->precio_paquete = $this->suscripcion ? $this->suscripcion->precio_paquete : 0.00;
            $this->precio_extra = $this->suscripcion ? $this->suscripcion->precio_extra : 0.00;
            $this->precio_total = $this->suscripcion ? $this->suscripcion->precio_total : 0.00;
            $this->descuento = $this->suscripcion ? $this->suscripcion->descuento : 0.00;
            $this->total = $this->suscripcion ? $this->suscripcion->total : 0.00;
            $this->modulos = $this->suscripcion->modulos->pluck('id')->map(fn($id) => (string)$id)->toArray();
            $this->sucursales = $this->suscripcion->sucursales()->pluck('id')->toArray();
            $this->terminales = $this->suscripcion->terminales()->pluck('id')->toArray();
            $this->usuarios = $this->suscripcion->usuarios()->pluck('id')->toArray();
        } else {
            $this->suscripcion = new Suscripcion();
        }

        if ($this->cliente_id) {
            $this->loadSucursales();
            $this->loadUsuarios();
            if (count($this->sucursales) > 0)
                $this->loadTerminales();
        }
        // Ejecutar el primer cálculo financiero al cargar
        // $this->calculatePricing();
    }

    public function hydrate()
    {
        $this->dispatchBrowserEvent('reApplySelect2');
    }

    public function updated($propertyName, $value)
    {
        // Si el usuario cambia manualmente un límite o módulo, rompemos el enlace al paquete base

        // if (in_array($propertyName, ['cant_sucurales', 'cant_terminales', 'cant_usuarios', 'modulos'])) {
        //     $this->paquete_id = '';
        // }
        if ($propertyName == 'cliente_id') {
            $this->sucursales = [];
            $this->terminales = [];
            $this->usuarios = [];
            $this->sucursalesDisponibles = [];
            $this->terminalesDisponibles = [];
            $this->usuariosDisponibles = [];
            $this->loadSucursales();
            $this->loadTerminales();
        }

        if ($propertyName == 'sucursales') {
            $this->terminales = [];
            $this->terminalesDisponibles = [];
            $this->loadTerminales();
        }

        // Si cambia el paquete, sincronizamos sus límites y módulos de inmediato
        if ($propertyName === 'paquete_id' && !empty($this->paquete_id)) {
            $package = Paquete::with('modulos')->find($this->paquete_id);
            if ($package) {
                $this->cant_sucursales = $package->cant_sucursales;
                $this->cant_terminales = $package->cant_terminales;
                $this->cant_usuarios = $package->cant_usuarios;
                $this->modulos = $package->modulos->pluck('id')->map(fn($id) => (string)$id)->toArray();
            }
        }

        $this->dispatchBrowserEvent('reApplySelect2');
        $this->calculatePricing();
    }

    public function getClienteProperty()
    {
        return $this->cliente_id ? Cliente::find($this->cliente_id) : null;
    }
    public function getModulosPaqueteProperty()
    {
        return $this->paquete_id ? Paquete::find($this->paquete_id)->modulos()->pluck('id')->toArray() : [];
    }

    public function getPorcentajeDescuentoProperty()
    {
        $subtotal = $this->precio_paquete + $this->precio_extra;

        if ($subtotal <= 0) {
            return 0;
        }

        return ($this->descuento / $subtotal) * 100;
    }

    public function calculatePricing()
    {
        if (!$this->globalSettings) {
            $this->globalSettings = Config::all()->pluck('valor', 'llave')->toArray();
        }

        // CASO 1: Basado en un Paquete Predefinido
        if (!empty($this->paquete_id)) {
            $package = Paquete::find($this->paquete_id);
            if ($package) {
                $this->precio_paquete = $package->precio;
                $this->precio_extra = 0.00;

                // Nota: Por diseño, al seleccionar un paquete los límites se igualan exactamente a él.
                // Si en el futuro permites excedentes manteniendo la etiqueta del paquete, esta lógica los sumará:
                if ($this->cant_sucursales > $package->cant_sucursales) {
                    $this->precio_extra += ($this->cant_sucursales - $package->cant_sucursales) * $this->globalSettings['precio_sucursal_adicional'];
                }
                if ($this->cant_terminales > $package->cant_terminales) {
                    $this->precio_extra += ($this->cant_terminales - $package->cant_terminales) * $this->globalSettings['precio_terminal_adicional'];
                }
                if ($this->cant_usuarios > $package->cant_usuarios) {
                    $this->precio_extra += ($this->cant_usuarios - $package->cant_usuarios) * $this->globalSettings['precio_usuario_adicional'];
                }

                $this->precio_extra += Modulo::whereIn('id', array_values(array_diff($this->modulos, $this->modulos_paquete)))->sum('costo_base');
            }
        } else {
            // CASO 2: Plan 100% Personalizado (Custom)
            $this->precio_paquete = 0.00;
            $this->precio_extra = 0.00;

            // Multiplicamos los límites configurados por las tarifas de la tabla de configuraciones de la DB
            $this->precio_extra += $this->cant_sucursales * $this->globalSettings['precio_sucursal_adicional'];
            $this->precio_extra += $this->cant_terminales * $this->globalSettings['precio_terminal_adicional'];
            $this->precio_extra += $this->cant_usuarios * $this->globalSettings['precio_usuario_adicional'];

            // Sumamos los costos bases individuales de cada módulo encendido
            $this->precio_extra += Modulo::whereIn('id', $this->modulos)->sum('costo_base');
        }

        $this->precio_total = $this->precio_paquete + $this->precio_extra;
        $this->total = $this->precio_total - max($this->descuento, 0);
    }

    public function incrementSucursales()
    {
        $this->cant_sucursales += 1;
        $this->dispatchBrowserEvent('reApplySelect2');
        $this->calculatePricing();
    }
    public function incrementTerminales()
    {
        $this->cant_terminales += 1;
        $this->dispatchBrowserEvent('reApplySelect2');
        $this->calculatePricing();
    }
    public function incrementUsuarios()
    {
        $this->cant_usuarios += 1;
        $this->dispatchBrowserEvent('reApplySelect2');
        $this->calculatePricing();
    }

    public function decrementSucursales()
    {
        if ($this->paquete_id) {
            $paquete = Paquete::find($this->paquete_id);
            if ($this->cant_sucursales > $paquete->cant_sucursales)
                $this->cant_sucursales -= 1;
        }
        $this->dispatchBrowserEvent('reApplySelect2');
        $this->calculatePricing();
    }
    public function decrementTerminales()
    {
        if ($this->paquete_id) {
            $paquete = Paquete::find($this->paquete_id);
            if ($this->cant_terminales > $paquete->cant_terminales)
                $this->cant_terminales -= 1;
        }
        $this->dispatchBrowserEvent('reApplySelect2');
        $this->calculatePricing();
    }
    public function decrementUsuarios()
    {
        if ($this->paquete_id) {
            $paquete = Paquete::find($this->paquete_id);
            if ($this->cant_usuarios > $paquete->cant_usuarios)
                $this->cant_usuarios -= 1;
        }
        $this->dispatchBrowserEvent('reApplySelect2');
        $this->calculatePricing();
    }
    public function resetSucursales()
    {
        if ($this->paquete_id) {
            $paquete = Paquete::find($this->paquete_id);
            $this->cant_sucursales = $paquete->cant_sucursales;
        }
        $this->dispatchBrowserEvent('reApplySelect2');
        $this->calculatePricing();
    }
    public function resetTerminales()
    {
        if ($this->paquete_id) {
            $paquete = Paquete::find($this->paquete_id);
            $this->cant_terminales = $paquete->cant_terminales;
        }
        $this->dispatchBrowserEvent('reApplySelect2');
        $this->calculatePricing();
    }
    public function resetUsuarios()
    {
        if ($this->paquete_id) {
            $paquete = Paquete::find($this->paquete_id);
            $this->cant_usuarios = $paquete->cant_usuarios;
        }
        $this->dispatchBrowserEvent('reApplySelect2');
        $this->calculatePricing();
    }

    public function loadSucursales()
    {
        $this->sucursalesDisponibles = Sucursal::where('cliente_id', $this->cliente_id)->whereDoesntHave('suscripcion')->orWhere('suscripcion_id', $this->suscripcion->id)->lazy()->map(function ($value) {
            return [
                'value' => $value->id,
                'label' => Crypt::decrypt($value->nombre_comercial)
            ];
        })->toArray();
    }

    public function loadTerminales()
    {
        $this->terminalesDisponibles = Terminal::whereIn('sucursal_id', $this->sucursales)
            ->whereDoesntHave('suscripcion')
            ->orWhere('suscripcion_id', $this->suscripcion->id)
            ->lazy()->map->only(['value', 'label'])->toArray();
    }

    public function loadUsuarios()
    {
        $this->usuariosDisponibles = User::where(function ($query) {
            $query->whereDoesntHave('suscripciones')
                ->Where('cliente_id', $this->cliente_id);
        })->whereNotNull('cliente_id')
            ->orWhereIn('id', Arr::wrap($this->usuarios))->lazy()->map->only(['value', 'label'])->toArray();
    }

    public function AddSucursal($id)
    {
        $this->sucursales[] = $id;
        $sucursal = Sucursal::find($id)->only(['value', 'label']);
        $sucursal['label'] = Crypt::decrypt($sucursal['label']);
        $this->sucursalesDisponibles[] = $sucursal;
    }
    public function AddTerminal($id)
    {
        $this->terminales[] = $id;
        $this->terminalesDisponibles[] = Terminal::find($id)->only(['value', 'label']);
    }
    public function AddUsuario($id)
    {
        $this->usuarios[] = $id;
        $this->usuariosDisponibles[] = User::find($id)->only(['value', 'label']);
    }

    public function submit()
    {
        $data = $this->validate();
        $this->calculatePricing(); // Forzar recalculación de seguridad antes de persistir

        $withErrors = false;
        if (count($data['sucursales']) > $this->cant_sucursales) {
            $this->addError('sucursales', __('subscription_branches_exceeded'));
            $withErrors = true;
        }
        if (count($data['terminales']) > $this->cant_terminales) {
            $this->addError('terminales', __('subscription_terminals_exceeded'));
            $withErrors = true;
        }
        if (count($data['usuarios']) > $this->cant_usuarios) {
            $this->addError('usuarios', __('subscription_users_exceeded'));
            $withErrors = true;
        }

        if ($withErrors)
            return;

        $this->suscripcion->fill(Arr::except($data, [
            'modulos',
            'sucursales',
            'terminales',
            'usuarios'
        ]))->save();

        // Sincronizamos los módulos asignados directamente con la Suscripción (Relación muchos a muchos)
        $this->suscripcion->modulos()->sync($this->modulos);

        $this->suscripcion->usuarios()->sync($this->usuarios);

        $this->suscripcion->sucursales()->update(['suscripcion_id' => null]);
        Sucursal::whereIn('id', $this->sucursales)->update(['suscripcion_id' => $this->suscripcion->id]);

        $this->suscripcion->terminales()->update(['suscripcion_id' => null]);
        Terminal::whereIn('id', $this->terminales)->update(['suscripcion_id' => $this->suscripcion->id]);

        $this->emit('show-toast', 'Subscripción guardada correctamente.', 'success');
    }

    public function render()
    {
        $clientes = Cliente::whereDoesntHave('suscripcion_pendiente')
            ->whereHas('direccion_fiscal', function ($query) {
                return $query->whereNotNull('codigo_postal')
                    ->where('codigo_postal', '!=', '');
            })
            ->where('es_cliente', 1)
            ->whereNotNull('razon_social')
            ->where('razon_social', '!=', '')
            ->whereNotNull('rfc')
            ->where('rfc', '!=', '')
            ->whereNotNull('contacto_nombre')
            ->where('contacto_nombre', '!=', '')
            ->whereNotNull('contacto_correo')
            ->where('contacto_correo', '!=', '')
            ->whereNotNull('contacto_telefono')
            ->where('contacto_telefono', '!=', '')
            ->whereNotNull('regimen_fiscal_id')
            ->get()->map(function ($value) {
                return [
                    'value' => $value->id,
                    'label' => Crypt::decrypt($value->nombre_comercial)
                ];
            })->toArray();

        return view('livewire.suscripciones.gestion-suscripciones', [
            'paquetes' => Paquete::all(),
            'modulosDisponibles' => Modulo::all(),
            'clientes' => $clientes
        ]);
    }
}
