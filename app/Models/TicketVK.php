<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class TicketVK
 * @package App\Models
 * @version January 12, 2021, 7:46 pm CST
 *
 * @property string $mesa
 * @property string $asiento
 * @property string $fecha_transaccion
 * @property integer $estado
 * @property string $id_transaccion
 * @property string $pos_ip
 * @property float $tiempo_resolver
 * @property float $porciento_alerta_estado
 * @property integer $empleado_id
 * @property integer $sucursal_id
 * @property integer $terminal_id
 * @property integer $departamento_id
 */
class TicketVK extends Model
{
    use LogsActivity;

    public $table = 'tb_tickets_vk';

    public $fillable = [
        'mesa',
        'asiento',
        'fecha_transaccion',
        'estado',
        'id_transaccion',
        'pos_ip',
        'tiempo_resolver',
        'porciento_alerta_estado',
        'empleado_id',
        'sucursal_id',
        'terminal_id',
        'departamento_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'mesa' => 'string',
        'asiento' => 'string',
        'fecha_transaccion' => 'string',
        'estado' => 'integer',
        'id_transaccion' => 'string',
        'pos_ip' => 'string',
        'tiempo_resolver' => 'float',
        'porciento_alerta_estado' => 'float',
        'empleado_id' => 'integer',
        'sucursal_id' => 'integer',
        'terminal_id' => 'integer',
        'departamento_id' => 'integer',
    ];

    /**
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => 'El Ticket VK ha sido creado.',
                    'updated' => 'El Ticket VK ha sido actualizado.',
                    'deleted' => 'El Ticket VK ha sido eliminado.',
                    'restored' => 'El Ticket VK ha sido restaurado.',
                    'forceDeleted' => 'El Ticket VK ha sido eliminado permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('Tickets VK')
            ->logExcept(['created_at', 'updated_at'])
            ->logOnlyDirty(); // Registra solo los campos que han cambiado
    }

    public function Departamento()
    {
        return $this->belongsTo(Departamento::class);
    }
    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function terminal()
    {
        return $this->belongsTo(Terminal::class);
    }
    public function items()
    {
        return $this->hasMany(ItemTicketVK::class, 'ticket_vk_id');
    }
}
