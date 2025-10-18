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
        Schema::create('programas_formacion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255); // Nombre del programa (Ej: "Análisis y Desarrollo de Software")
            $table->string('ficha', 100)->unique(); // Número de ficha (Ej: "2558346")
            $table->date('fecha_inicio'); // Fecha de inicio del programa
            $table->date('fecha_fin'); // Fecha de finalización del programa
            $table->string('nivel_formacion', 100)->nullable(); // Tecnólogo, Técnico, etc.
            $table->boolean('activo')->default(true); // Estado del programa
            $table->text('descripcion')->nullable(); // Descripción opcional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programas_formacion');
    }
};
