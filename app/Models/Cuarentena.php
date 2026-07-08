<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Cuarentena
 * @package App\Models\Administracion\CodificadoresFacturacion
 * @version January 12, 2021, 7:46 pm CST
 *
 * @property string $texto
 * @property string $ip
 * @property string $data
 * @property integer $terminal_id
 * @property integer $sucursal_id
 * @property integer $cliente_id
 */
class Cuarentena extends Model
{
    use LogsActivity;
    public $table = 'tb_cuarentena';

    public $fillable = [
        'texto',
        'ip',
        'data',
        'terminal_id',
        'sucursal_id',
        'cliente_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'texto' => 'string',
        'ip' => 'string',
        'data' => 'string',
        'terminal_id' => 'integer',
        'sucursal_id' => 'integer',
        'cliente_id' => 'integer',
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
                    'created' => 'Ha entrado un nuevo ticket a cuarentena.',
                    'updated' => 'Se ha modificado un ticket en cuarentena.',
                    'deleted' => 'Se ha eliminado un ticket de cuarentena.',
                    default => $eventName,
                };
            })
            ->useLogName('Tickets en Cuarentena')
            ->logExcept(['created_at', 'updated_at'])
            ->logOnlyDirty(); // Registra solo los campos que han cambiado
    }

    public function terminal()
    {
        return $this->belongsTo(Terminal::class);
    }
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
