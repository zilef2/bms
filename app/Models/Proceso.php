<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proceso extends Model {
	
	/** @use HasFactory<\Database\Factories\ProcesoFactory> */
	use HasFactory;
	
	protected $fillable = [
		'id',
		'llave_proceso',
		'idProceso',
		'id_conexion',
		'fecha_proceso',
		'fecha_ultima_actuacion',
		'despacho',
		'departamento',
		'sujetos_procesales',
		'es_privado',
		'cant_filas',
		'validacioncini',
		'pdf_name',
		'pdf_size',
		'pdf_sumarized',
		'pdf_path',
		'Numprocesos',
	];
	
	protected $casts = [
        'sujetos_procesales' => 'array', // Esto lo convierte en un array de PHP
    ];
	
}
