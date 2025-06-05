<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Config
 * @package App\Models\Administracion
 * @version May 6, 2019, 7:37 pm UTC
 *
 * @property string llave
 * @property string valor
 */
class Config extends Model
{
    use LogsActivity;

    public $table = 'tb_configs';

    public $timestamps = false;

    protected $primaryKey = 'llave';

    public $fillable = [
        'llave',
        'valor'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'llave' => 'string',
        'valor' => 'string'
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
                    'created' => 'La Configuración del Sistema ha sido creada.',
                    'updated' => 'La Configuración del Sistema ha sido actualizada.',
                    'deleted' => 'La Configuración del Sistema ha sido eliminada.',
                    'restored' => 'La Configuración del Sistema ha sido restaurada.',
                    'forceDeleted' => 'La Configuración del Sistema ha sido eliminada permanentemente.',
                    default => $eventName,
                };
            })
            ->useLogName('Configuración del Sistema')
            ->logOnlyDirty(); // Registra solo los campos que han cambiado
    }

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'llave' => 'required'
    ];
}
