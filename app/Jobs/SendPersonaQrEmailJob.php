<?php

namespace App\Jobs;

use App\Mail\PersonaQrMailable;
use App\Models\Persona;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SendPersonaQrEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    public $backoff = [10, 30, 60]; // Esperar 10s, 30s, 60s entre reintentos

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $personaId,
        public string $correo
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("📧 Enviando email con QR", [
                'persona_id' => $this->personaId,
                'correo' => $this->correo,
                'attempt' => $this->attempts()
            ]);

            // Cargar la persona con sus relaciones
            $persona = Persona::with('portatiles')->findOrFail($this->personaId);

            // Verificar que los archivos QR existan físicamente
            $qrReady = $this->checkQrFilesExist($persona);

            if (!$qrReady) {
                // Si es el primer intento, esperar un poco y reintentar
                if ($this->attempts() === 1) {
                    Log::warning("⏳ QR aún no está listo, esperando...", [
                        'persona_id' => $this->personaId,
                        'attempt' => $this->attempts()
                    ]);
                    
                    // Liberar el job para reintentarlo en 15 segundos
                    $this->release(15);
                    return;
                }
                
                Log::warning("⚠️ Enviando email sin algunos archivos QR adjuntos", [
                    'persona_id' => $this->personaId,
                    'attempt' => $this->attempts()
                ]);
            }

            // Enviar el email
            Mail::to($this->correo)->send(new PersonaQrMailable($persona));

            Log::info("✅ Email enviado exitosamente", [
                'persona_id' => $this->personaId,
                'correo' => $this->correo
            ]);

        } catch (\Throwable $e) {
            Log::error("❌ Error enviando email con QR", [
                'persona_id' => $this->personaId,
                'correo' => $this->correo,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            throw $e;
        }
    }

    /**
     * Verificar que los archivos QR existan físicamente
     */
    private function checkQrFilesExist(Persona $persona): bool
    {
        $allExist = true;

        // Verificar QR de la persona
        if ($persona->qrCode) {
            $relative = ltrim(str_replace('/storage/', '', $persona->qrCode), '/');
            $fullPath = storage_path('app/public/' . $relative);
            
            if (!is_file($fullPath)) {
                Log::debug("QR de persona no existe aún: {$fullPath}");
                $allExist = false;
            }
        }

        // Verificar QR de portátiles
        foreach ($persona->portatiles as $portatil) {
            if ($portatil->qrCode) {
                $relative = ltrim(str_replace('/storage/', '', $portatil->qrCode), '/');
                $fullPath = storage_path('app/public/' . $relative);
                
                if (!is_file($fullPath)) {
                    Log::debug("QR de portátil no existe aún: {$fullPath}");
                    $allExist = false;
                }
            }
        }

        return $allExist;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical("❌ Job de envío de email falló después de {$this->tries} intentos", [
            'persona_id' => $this->personaId,
            'correo' => $this->correo,
            'error' => $exception->getMessage()
        ]);
    }
}
