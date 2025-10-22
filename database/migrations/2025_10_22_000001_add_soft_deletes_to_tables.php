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
        // Personas
        if (Schema::hasTable('personas') && !Schema::hasColumn('personas', 'deleted_at')) {
            Schema::table('personas', function (Blueprint $table) {
                $table->softDeletes();
                $table->index('deleted_at');
            });
        }

        // Usuarios Sistema
        if (Schema::hasTable('usuarios_sistema') && !Schema::hasColumn('usuarios_sistema', 'deleted_at')) {
            Schema::table('usuarios_sistema', function (Blueprint $table) {
                $table->softDeletes();
                $table->index('deleted_at');
            });
        }

        // Portátiles
        if (Schema::hasTable('portatiles') && !Schema::hasColumn('portatiles', 'deleted_at')) {
            Schema::table('portatiles', function (Blueprint $table) {
                $table->softDeletes();
                $table->index('deleted_at');
            });
        }

        // Vehículos
        if (Schema::hasTable('vehiculos') && !Schema::hasColumn('vehiculos', 'deleted_at')) {
            Schema::table('vehiculos', function (Blueprint $table) {
                $table->softDeletes();
                $table->index('deleted_at');
            });
        }

        // Programas de Formación
        if (Schema::hasTable('programas_formacion') && !Schema::hasColumn('programas_formacion', 'deleted_at')) {
            Schema::table('programas_formacion', function (Blueprint $table) {
                $table->softDeletes();
                $table->index('deleted_at');
            });
        }

        // Accesos - NO soft delete (mantener historial completo)
        // Incidencias - NO soft delete (mantener historial completo)
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('personas', 'deleted_at')) {
            Schema::table('personas', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('usuarios_sistema', 'deleted_at')) {
            Schema::table('usuarios_sistema', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('portatiles', 'deleted_at')) {
            Schema::table('portatiles', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('vehiculos', 'deleted_at')) {
            Schema::table('vehiculos', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('programas_formacion', 'deleted_at')) {
            Schema::table('programas_formacion', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
