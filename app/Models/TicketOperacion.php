<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class TicketOperacion
 * @package App\Models
 * @version January 12, 2021, 7:55 pm CST
 *
 * @property string $nombre
 * @property float $monto
 * @property float $propina
 * @property integer $ticket_id
 * @property integer $sucursal_forma_pago_id
 * @property integer $factura_id
 * @property integer $empleado_id
 */
class TicketOperacion extends Model
{
    public $table = 'tb_ticket_operaciones';

    public $fillable = [
        'nombre',
        'monto',
        'propina',
        'ticket_id',
        'sucursal_forma_pago_id',
        'factura_id',
        'empleado_id',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function forma_pago()
    {
        return $this->belongsTo(SucursalFormaPago::class, 'sucursal_forma_pago_id');
    }
    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }
    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }
}
