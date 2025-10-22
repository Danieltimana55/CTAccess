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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            
            // Usuario que realizó la acción
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('usuarios_sistema', 'idUsuario')
                ->nullOnDelete();
            $table->string('usuario_nombre')->nullable(); // Cache del nombre
            $table->string('usuario_rol')->nullable(); // Cache del rol
            
            // Modelo afectado
            $table->string('model_type')->nullable(); // App\Models\Persona
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('model_name')->nullable(); // Nombre/título del registro
            
            // Acción realizada
            $table->string('action', 50); // created, updated, deleted, restored, viewed, exported, etc.
            $table->string('module', 50)->nullable(); // personas, usuarios, accesos, etc.
            $table->text('description')->nullable(); // Descripción legible
            
            // Datos del cambio
            $table->json('old_values')->nullable(); // Valores anteriores
            $table->json('new_values')->nullable(); // Valores nuevos
            $table->json('properties')->nullable(); // Metadata adicional
            
            // Información de contexto
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable(); // GET, POST, PUT, DELETE
            $table->integer('status_code')->nullable(); // 200, 404, 500, etc.
            
            // Geolocalización (opcional)
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            
            // Nivel de severidad
            $table->enum('severity', ['info', 'warning', 'error', 'critical'])->default('info');
            
            // Timestamps
            $table->timestamp('created_at')->nullable();
            
            // Índices para performance
            $table->index(['usuario_id', 'created_at']);
            $table->index(['model_type', 'model_id']);
            $table->index(['action', 'created_at']);
            $table->index(['module', 'created_at']);
            $table->index('ip_address');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
