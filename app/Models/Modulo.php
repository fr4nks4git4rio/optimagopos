<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
