<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class TicketProducto
 * @package App\Models
 * @version January 12, 2021, 7:55 pm CST
 *
 * @property float precio
 * @property integer cantidad
 * @property float descuento
 * @property integer ticket_id
 * @property integer producto_id
 * @property integer departamento_id
 */
class TicketProducto extends Model
{
    public $table = 'tb_ticket_productos';

    public $fillable = [
        'precio',
        'cantidad',
        'descuento',
        'ticket_id',
        'producto_id',
        'departamento_id'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function correcciones()
    {
        return $this->hasMany(TicketProductoCorreccion::class, 'producto_id');
    }
}
