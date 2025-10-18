<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Jornada;
use Illuminate\Support\Facades\DB;

class JornadaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar tabla si existe (desactivando temporalmente las foreign keys)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('jornadas')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $jornadas = [
            [
                'nombre' => 'Mañana',
                'descripcion' => 'Jornada matutina de 6:00 AM a 12:00 PM',
                'hora_inicio' => '06:00:00',
                'hora_fin' => '12:00:00',
                'dias_semana' => [1, 2, 3, 4, 5, 6], // Lunes a Sábado
                'activa' => true,
            ],
            [
                'nombre' => 'Mixta',
                'descripcion' => 'Jornada mixta de 12:00 PM a 6:00 PM',
                'hora_inicio' => '12:00:00',
                'hora_fin' => '18:00:00',
                'dias_semana' => [1, 2, 3, 4, 5, 6], // Lunes a Sábado
                'activa' => true,
            ],
            [
                'nombre' => 'Nocturna',
                'descripcion' => 'Jornada nocturna de 6:00 PM a 10:00 PM',
                'hora_inicio' => '18:00:00',
                'hora_fin' => '22:00:00',
                'dias_semana' => [1, 2, 3, 4, 5, 6], // Lunes a Sábado
                'activa' => true,
            ],
        ];

        foreach ($jornadas as $jornada) {
            Jornada::create($jornada);
        }

        $this->command->info('✅ 3 jornadas creadas exitosamente');
        $this->command->info('   • Mañana: 6:00 AM - 12:00 PM');
        $this->command->info('   • Mixta: 12:00 PM - 6:00 PM');
        $this->command->info('   • Nocturna: 6:00 PM - 10:00 PM');
    }
}
