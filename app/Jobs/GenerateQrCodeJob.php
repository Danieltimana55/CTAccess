<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Persona;
use App\Models\Portatiles;

class GenerateQrCodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Reintentar 3 veces si falla
    public $timeout = 60; // Timeout de 60 segundos

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $type, // 'persona' o 'portatil'
        public int $id,
        public string $qrContent,
        public string $filePath
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("🔄 Generando QR en segundo plano", [
                'type' => $this->type,
                'id' => $this->id,
                'content' => $this->qrContent,
                'path' => $this->filePath
            ]);

            // Generar el QR usando el servicio externo
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($this->qrContent);
            $response = Http::timeout(30)->get($qrUrl);

            if (!$response->ok()) {
                throw new \RuntimeException('Error al generar QR: HTTP ' . $response->status());
            }

            // Guardar el archivo en la ruta predefinida
            Storage::disk('public')->put($this->filePath, $response->body());

            Log::info("✅ QR generado exitosamente", [
                'type' => $this->type,
                'id' => $this->id,
                'path' => $this->filePath,
                'size' => strlen($response->body())
            ]);

        } catch (\Throwable $e) {
            Log::error("❌ Error generando QR en segundo plano", [
                'type' => $this->type,
                'id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Relanzar la excepción para que Laravel reintente el job
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical("❌ Job de generación QR falló después de {$this->tries} intentos", [
            'type' => $this->type,
            'id' => $this->id,
            'error' => $exception->getMessage()
        ]);
    }
}
