<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Summary of Paquete
 *
 * @property string $nombre
 * @property string $descripcion
 * @property string $precio
 * @property string $cant_sucursales
 * @property string $cant_terminales
 * @property string $cant_usuarios
 * @property string $cant_timbres
 * @property string $cant_meses_analitica_basica
 */
class Paquete extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'tb_paquetes';

    public $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'cant_sucursales',
        'cant_terminales',
        'cant_usuarios',
        'cant_timbres',
        'cant_meses_analitica_basica',
    ];

    public function modulos()
    {
        return $this->belongsToMany(Modulo::class, 'tb_paquetes_modulos', 'paquete_id', 'modulo_id');
    }
    public function suscripciones()
    {
        return $this->hasMany(Suscripcion::class, 'paquete_id');
    }
}
