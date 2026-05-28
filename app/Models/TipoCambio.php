<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class TipoCambio
 * @package App\Models\Administracion
 * @version May 6, 2019, 7:37 pm UTC
 *
 * @property float $tasa
 * @property integer $from_id
 * @property integer $to_id
 * @property integer $sucursal_id
 * @property integer $cliente_id
 */
class TipoCambio extends Model
{
    use LogsActivity;

    public $table = 'tb_tipo_cambios';

    public $fillable = [
        'tasa',
        'from_id',
        'to_id',
        'sucursal_id',
        'cliente_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'tasa' => 'float',
        'from_id' => 'integer',
        'to_id' => 'integer',
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
                    'created' => 'El Tipo de Cambio ha sido creado.',
                    'updated' => 'El Tipo de Cambio ha sido actualizado.',
                    'deleted' => 'El Tipo de Cambio ha sido eliminado.',
                    'restored' => 'El Tipo de Cambio ha sido restaurado.',
                    'forceDeleted' => 'El Tipo de Cambio ha sido eliminado permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('Tipo de Cambio')
            ->logExcept(['created_at', 'updated_at'])
            ->logOnlyDirty(); // Registra solo los campos que han cambiado
    }

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'tasa' => 'required',
        'from_id' => 'required',
        'to_id' => 'required',
        'sucursal_id' => 'required',
        'cliente_id' => 'required',
    ];
}
