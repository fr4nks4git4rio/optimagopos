<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Summary of Modulo
 *
 * @property string $icono
 * @property string $icono_color
 * @property string $nombre
 * @property string $descripcion
 * @property integer $cant_funciones
 * @property float $costo_base
 */
class Modulo extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'tb_modulos';

    public $fillable = [
        'icono',
        'icono_color',
        'nombre',
        'descripcion',
        'cant_funciones',
        'costo_base'
    ];

    public function paquetes()
    {
        return $this->belongsToMany(Paquete::class, 'tb_paquetes_modulos', 'modulo_id', 'paquete_id');
    }
    public function suscripciones()
    {
        return $this->belongsToMany(Suscripcion::class, 'tb_suscripciones_modulos', 'modulo_id', 'paquete_id');
    }
}
