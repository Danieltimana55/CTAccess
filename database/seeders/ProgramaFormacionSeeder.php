<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProgramaFormacion;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProgramaFormacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar tabla (desactivando foreign keys temporalmente)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('programas_formacion')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $programas = [
            [
                'nombre' => 'Análisis y Desarrollo de Software',
                'ficha' => '2558346',
                'fecha_inicio' => Carbon::create(2023, 1, 15),
                'fecha_fin' => Carbon::create(2025, 1, 15),
                'nivel_formacion' => 'Tecnólogo',
                'activo' => true,
                'descripcion' => 'Programa de formación en desarrollo de software, bases de datos y programación'
            ],
            [
                'nombre' => 'Gestión Administrativa',
                'ficha' => '2558347',
                'fecha_inicio' => Carbon::create(2023, 3, 1),
                'fecha_fin' => Carbon::create(2025, 3, 1),
                'nivel_formacion' => 'Tecnólogo',
                'activo' => true,
                'descripcion' => 'Programa enfocado en administración de empresas y gestión organizacional'
            ],
            [
                'nombre' => 'Mantenimiento Electrónico e Instrumental Industrial',
                'ficha' => '2558348',
                'fecha_inicio' => Carbon::create(2023, 2, 10),
                'fecha_fin' => Carbon::create(2025, 8, 10),
                'nivel_formacion' => 'Tecnólogo',
                'activo' => true,
                'descripcion' => 'Formación en electrónica industrial y mantenimiento de equipos'
            ],
            [
                'nombre' => 'Gestión Logística',
                'ficha' => '2558349',
                'fecha_inicio' => Carbon::create(2024, 1, 20),
                'fecha_fin' => Carbon::create(2026, 1, 20),
                'nivel_formacion' => 'Tecnólogo',
                'activo' => true,
                'descripcion' => 'Programa de cadena de suministro y logística empresarial'
            ],
            [
                'nombre' => 'Diseño Gráfico',
                'ficha' => '2558350',
                'fecha_inicio' => Carbon::create(2024, 4, 15),
                'fecha_fin' => Carbon::create(2025, 10, 15),
                'nivel_formacion' => 'Técnico',
                'activo' => true,
                'descripcion' => 'Formación técnica en diseño visual y comunicación gráfica'
            ],
            [
                'nombre' => 'Contabilidad y Finanzas',
                'ficha' => '2558351',
                'fecha_inicio' => Carbon::create(2022, 8, 1),
                'fecha_fin' => Carbon::create(2024, 8, 1),
                'nivel_formacion' => 'Tecnólogo',
                'activo' => false, // Ya finalizó
                'descripcion' => 'Programa de contabilidad financiera y tributaria'
            ],
        ];

        foreach ($programas as $programa) {
            ProgramaFormacion::create($programa);
        }

        $this->command->info('✅ ' . count($programas) . ' programas de formación creados exitosamente');
        $this->command->info('   • Análisis y Desarrollo de Software - Ficha 2558346');
        $this->command->info('   • Gestión Administrativa - Ficha 2558347');
        $this->command->info('   • Mantenimiento Electrónico - Ficha 2558348');
        $this->command->info('   • Gestión Logística - Ficha 2558349');
        $this->command->info('   • Diseño Gráfico - Ficha 2558350');
        $this->command->info('   • Contabilidad y Finanzas - Ficha 2558351 (Finalizado)');
    }
}
