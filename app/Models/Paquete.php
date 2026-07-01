<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    ];

    public function modulos()
    {
        return $this->belongsToMany(Modulo::class, 'tb_paquete_modulos', 'paquete_id', 'modulo_id');
    }
}
