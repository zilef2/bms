<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('procesos', function (Blueprint $table) {
            $table->string('llave_proceso',30)->nullable();
            $table->string('idProceso',128)->nullable();
            $table->text('id_conexion')->nullable();
            $table->dateTime('fecha_proceso')->default(now())->nullable();
            $table->dateTime('fecha_ultima_actuacion')->default(now())->nullable();
            $table->string('despacho')->nullable();
            $table->string('departamento')->nullable();
            $table->text('sujetos_procesales')->nullable();
            $table->string('es_privado')->nullable();
            $table->integer('cant_filas')->nullable();
            $table->integer('Numprocesos')->nullable();
            $table->boolean('validacioncini')->default(false)->nullable();
            $table->string('pdf_name')->nullable();
            $table->string('pdf_size')->nullable();
            $table->string('pdf_sumarized')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procesos');
    }
};
