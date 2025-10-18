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
        Schema::create('jornadas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique(); // Mañana, Mixta, Nocturna
            $table->string('descripcion')->nullable();
            $table->time('hora_inicio'); // Hora de inicio de la jornada
            $table->time('hora_fin'); // Hora de fin de la jornada
            $table->json('dias_semana')->nullable(); // [1,2,3,4,5,6] = Lunes a Sábado
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jornadas');
    }
};
