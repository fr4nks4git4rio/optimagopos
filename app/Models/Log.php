<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Cfdi
 * @package App\Models\Administracion\CodificadoresFacturacion
 * @version January 12, 2021, 7:46 pm CST
 *
 * @property string $log
 * @property string $data
 * @property integer $status
 * @property integer $sucursal_id
 */
class Log extends Model
{
    public $table = 'tb_logs';

    public $fillable = [
        'log',
        'data',
        'status',
        'sucursal_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'log' => 'string',
        'data' => 'string',
        'status' => 'integer',
        'sucursal_id' => 'integer'
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }
}
