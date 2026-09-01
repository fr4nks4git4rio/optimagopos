<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TicketMovimientoCaja
 * @package App\Models
 * @version January 12, 2021, 7:55 pm CST
 *
 * @property string $nombre
 * @property float $monto
 * @property integer $ticket_id
 * @property integer $sucursal_forma_pago_id
 */
class TicketMovimientoCaja extends Model
{
    public $table = 'tb_ticket_movimientos_caja';

    public $fillable = [
        'nombre',
        'monto',
        'ticket_id',
        'sucursal_forma_pago_id',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function forma_pago()
    {
        return $this->belongsTo(SucursalFormaPago::class, 'sucursal_forma_pago_id');
    }
}
