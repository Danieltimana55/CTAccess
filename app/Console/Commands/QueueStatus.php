<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class QueueStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Muestra el estado actual de las colas y jobs pendientes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('═══════════════════════════════════════');
        $this->info('   Estado del Sistema de Colas');
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        // Obtener estadísticas de la base de datos
        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();

        // Mostrar información
        $this->info("📊 Jobs Pendientes: {$pendingJobs}");
        $this->info("❌ Jobs Fallidos: {$failedJobs}");
        $this->newLine();

        // Mostrar jobs pendientes si existen
        if ($pendingJobs > 0) {
            $this->warn('⏳ Hay jobs pendientes de procesar');
            $this->warn('   Asegúrate de tener un worker corriendo: php artisan queue:work');
            $this->newLine();

            // Mostrar los primeros 5 jobs
            $jobs = DB::table('jobs')
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get(['id', 'queue', 'attempts', 'created_at']);

            if ($jobs->count() > 0) {
                $this->table(
                    ['ID', 'Cola', 'Intentos', 'Creado'],
                    $jobs->map(fn($job) => [
                        $job->id,
                        $job->queue,
                        $job->attempts,
                        date('Y-m-d H:i:s', $job->created_at)
                    ])
                );
            }
        } else {
            $this->info('✅ No hay jobs pendientes');
        }

        // Mostrar jobs fallidos si existen
        if ($failedJobs > 0) {
            $this->newLine();
            $this->error("⚠️  Hay {$failedJobs} jobs fallidos");
            $this->error('   Revisa los errores con: php artisan queue:failed');
            $this->error('   Reintentar todos: php artisan queue:retry all');
            
            // Mostrar los primeros 3 jobs fallidos
            $failed = DB::table('failed_jobs')
                ->orderBy('failed_at', 'desc')
                ->limit(3)
                ->get(['id', 'queue', 'exception', 'failed_at']);

            if ($failed->count() > 0) {
                $this->newLine();
                $this->warn('Últimos jobs fallidos:');
                foreach ($failed as $job) {
                    $this->line("  ID: {$job->id} | Cola: {$job->queue} | Falló: {$job->failed_at}");
                    $exceptionPreview = substr($job->exception, 0, 100);
                    $this->line("  Error: {$exceptionPreview}...");
                    $this->newLine();
                }
            }
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info('Comandos útiles:');
        $this->info('  • php artisan queue:work          → Iniciar worker');
        $this->info('  • php artisan queue:failed        → Ver jobs fallidos');
        $this->info('  • php artisan queue:retry all     → Reintentar fallidos');
        $this->info('  • php artisan queue:flush         → Limpiar fallidos');
        $this->info('═══════════════════════════════════════');

        return Command::SUCCESS;
    }
}
