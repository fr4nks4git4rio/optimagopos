<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class TicketPropina
 * @package App\Models
 * @version January 12, 2021, 7:55 pm CST
 *
 * @property float monto
 * @property integer ticket_id
 * @property integer empleado_id
 */
class TicketPropina extends Model
{
    public $table = 'tb_ticket_propinas';

    public $fillable = [
        'monto',
        'ticket_id',
        'empleado_id'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }
}
