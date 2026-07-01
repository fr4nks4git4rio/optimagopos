<?php

namespace App\Http\Livewire\Clientes;

use App\Http\Livewire\Layouts\Modal;
use App\Models\Cliente;
use App\Models\Config;
use App\Models\Modulo;
use App\Models\Paquete;
use App\Models\Suscripcion;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GestionSuscripciones extends Component
{
    public Suscripcion $suscripcion;

    // Propiedades del formulario / suscripción
    public Cliente $cliente;
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

    // Objeto para almacenar la configuración global de la base de datos
    public $globalSettings;

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
        ];
    }

    protected $messages = [
        'cliente_id.required' => 'Seleccione el Cliente.',
        'cliente_id.exists' => 'Cliente no encontrado.',
        'fecha_inicio_operaciones.required' => 'La fecha de inicio de operaciones es obligatoria.',
        'fecha_inicio_pagos.required' => 'La fecha de próximo cobro es obligatoria.',
        'periodicidad_pagos.in' => 'Período no encontrado'
    ];

    public function mount($clienteId = null)
    {
        $this->cliente_id = $clienteId;
        // Cargamos el cliente junto a su suscripción activa y los módulos de dicha suscripción
        if ($this->cliente_id)
            $this->cliente = Cliente::with('suscripcion_activa.modulos')->findOrFail($this->cliente_id);

        // Traer las configuraciones globales de precios por excedentes
        $this->globalSettings = Config::all()->pluck('valor', 'llave')->toArray();

        if (isset($this->cliente) && ($this->cliente->suscripcion_pendiente || $this->cliente->suscripcion_activa)) {
            $this->suscripcion = $this->cliente->suscripcion_pendiente ?? $this->cliente->suscripcion_activa;
            $this->paquete_id = $this->suscripcion->paquete_id ?? '';
            $this->cant_sucursales = $this->suscripcion->cant_sucursales;
            $this->cant_terminales = $this->suscripcion->cant_terminales;
            $this->cant_usuarios = $this->suscripcion->cant_usuarios;
            $this->fecha_inicio_operaciones = $this->suscripcion->fecha_inicio_operaciones ? $this->suscripcion->fecha_inicio_operaciones->format('Y-m-d') : today()->format('Y-m-d');
            $this->fecha_inicio_pagos = $this->suscripcion->fecha_inicio_pagos ? $this->suscripcion->fecha_inicio_pagos->format('Y-m-d') : today()->format('Y-m-d');
            $this->periodicidad_pagos = $sub->periodicidad_pagos ?? 'MENSUAL';
            $this->estado = $sub->estado ?? 'PENDIENTE';
            $this->modulos = $this->suscripcion->modulos->pluck('id')->map(fn($id) => (string)$id)->toArray();
        }

        // Ejecutar el primer cálculo financiero al cargar
        $this->calculatePricing();
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
            if ($value)
                $this->cliente = Cliente::with('suscripcion_activa.modulos')->findOrFail($value);
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
    public function getModulosPaqueteProperty()
    {
        return $this->paquete_id ? Paquete::find($this->paquete_id)->modulos()->pluck('id')->toArray() : [];
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
    }

    public function incrementSucursales()
    {
        $this->cant_sucursales += 1;
        $this->calculatePricing();
    }
    public function incrementTerminales()
    {
        $this->cant_terminales += 1;
        $this->calculatePricing();
    }
    public function incrementUsuarios()
    {
        $this->cant_usuarios += 1;
        $this->calculatePricing();
    }

    public function decrementSucursales()
    {
        if ($this->paquete_id) {
            $paquete = Paquete::find($this->paquete_id);
            if ($this->cant_sucursales > $paquete->cant_sucursales)
                $this->cant_sucursales -= 1;
        }
        $this->calculatePricing();
    }
    public function decrementTerminales()
    {
        if ($this->paquete_id) {
            $paquete = Paquete::find($this->paquete_id);
            if ($this->cant_terminales > $paquete->cant_terminales)
                $this->cant_terminales -= 1;
        }
        $this->calculatePricing();
    }
    public function decrementUsuarios()
    {
        if ($this->paquete_id) {
            $paquete = Paquete::find($this->paquete_id);
            if ($this->cant_usuarios > $paquete->cant_usuarios)
                $this->cant_usuarios -= 1;
        }
        $this->calculatePricing();
    }
    public function resetSucursales()
    {
        if ($this->paquete_id) {
            $paquete = Paquete::find($this->paquete_id);
            $this->cant_sucursales = $paquete->cant_sucursales;
        }
        $this->calculatePricing();
    }
    public function resetTerminales()
    {
        if ($this->paquete_id) {
            $paquete = Paquete::find($this->paquete_id);
            $this->cant_terminales = $paquete->cant_terminales;
        }
        $this->calculatePricing();
    }
    public function resetUsuarios()
    {
        if ($this->paquete_id) {
            $paquete = Paquete::find($this->paquete_id);
            $this->cant_usuarios = $paquete->cant_usuarios;
        }
        $this->calculatePricing();
    }

    public function submit()
    {
        $this->validate();
        $this->calculatePricing(); // Forzar recalculación de seguridad antes de persistir

        // Guardamos o actualizamos en la tabla 'subscriptions' usando la relación del Cliente
        $subscription = $this->cliente->suscripciones()->updateOrCreate(
            ['estado' => $this->estado], // Busca la suscripción activa actual para pisarla
            [
                'paquete_id' => $this->paquete_id ?: null,
                'cant_sucursales' => $this->cant_sucursales,
                'cant_terminales' => $this->cant_terminales,
                'cant_usuarios' => $this->cant_usuarios,
                'fecha_inicio_operaciones' => $this->fecha_inicio_operaciones,
                'fecha_inicio_pagos' => $this->fecha_inicio_pagos,
                'periodicidad_pagos' => $this->periodicidad_pagos,
                // Registramos los históricos de precios de esta venta
                'precio_paquete' => $this->precio_paquete,
                'precio_extra' => $this->precio_extra,
                'precio_total' => $this->precio_total,
            ]
        );

        // Sincronizamos los módulos asignados directamente con la Suscripción (Relación muchos a muchos)
        $subscription->modulos()->sync($this->modulos);

        $this->emit('show-toast', 'Subscripción guardada correctamente.', 'success');
    }

    public function render()
    {
        $clientes = Cliente::whereDoesntHave('suscripcion_pendiente')
            ->whereDoesntHave('suscripcion_activa')
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

        return view('livewire.clientes.gestion-suscripciones', [
            'paquetes' => Paquete::all(),
            'modulosDisponibles' => Modulo::all(),
            'clientes' => $clientes
        ]);
    }
}
