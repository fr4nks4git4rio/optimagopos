<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Ticket
 * @package App\Models
 * @version January 12, 2021, 7:46 pm CST
 *
 * @property string ubicacion
 * @property string id_transaccion
 * @property string fecha_transaccion
 * @property float importe
 * @property float propina
 * @property string moneda
 * @property integer empleado_id
 * @property integer sucursal_id
 * @property integer terminal_id
 * @property integer factura_id
 */
class Ticket extends Model
{
    use LogsActivity;

    public $table = 'tb_tickets';

    public $fillable = [
        'ubicacion',
        'id_transaccion',
        'fecha_trasaccion',
        'importe',
        'propina',
        'moneda',
        'empleado_id',
        'sucursal_id',
        'terminal_id',
        'factura_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'ubicacion' => 'string',
        'id_transaccion' => 'string',
        'fecha_trasaccion' => 'string',
        'propina' => 'float',
        'importe' => 'float',
        'moneda' => 'string',
        'empleado_id' => 'integer',
        'sucursal_id' => 'integer',
        'terminal_id' => 'integer',
        'factura_id' => 'integer',
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
                    'created' => 'El Ticket ha sido creado.',
                    'updated' => 'El Ticket ha sido actualizado.',
                    'deleted' => 'El Ticket ha sido eliminado.',
                    'restored' => 'El Ticket ha sido restaurado.',
                    'forceDeleted' => 'El Ticket ha sido eliminado permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('Tickets')
            ->logExcept(['created_at', 'updated_at'])
            ->logOnlyDirty(); // Registra solo los campos que han cambiado
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

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    public function operaciones()
    {
        return $this->hasMany(TicketOperacion::class, 'ticket_id');
    }
    public function impuestos()
    {
        return $this->hasMany(TicketImpuesto::class, 'ticket_id');
    }

    public function propinas()
    {
        return $this->hasMany(TicketPropina::class, 'ticket_id');
    }

    public function productos()
    {
        return $this->hasMany(TicketProducto::class, 'ticket_id');
    }
}
