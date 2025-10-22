<?php

namespace App\Services;

use App\Models\Persona;
use App\Models\Portatil;
use App\Models\Portatiles;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Jobs\GenerateQrCodeJob;

class PersonaService
{
    /**
     * 🚀 OPTIMIZADO: Solo genera la ruta del QR sin crear el archivo.
     * El archivo se creará en segundo plano mediante un Job.
     * Retorna: ['url' => '/storage/qrcodes/...', 'path' => 'qrcodes/...']
     */
    protected function generateQrPath(string $content, string $prefix = 'qr'): array
    {
        $filename = sprintf('%s_%s_%s.png', $prefix, Str::slug($content), Str::random(8));
        $path = 'qrcodes/' . $filename; // Ruta relativa dentro del disco public
        $url = Storage::url($path); // URL pública /storage/qrcodes/...
        
        return [
            'url' => $url,
            'path' => $path
        ];
    }

    /**
     * 🔧 LEGACY: Descargar imagen PNG de un QR externo (solo para actualizaciones).
     * Retorna la URL pública /storage/qrcodes/....png
     */
    protected function storeQrPng(string $content, string $prefix = 'qr'): string
    {
        // Construir URL del servicio externo
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($content);
        $response = Http::get($qrUrl);
        if (!$response->ok()) {
            throw new \RuntimeException('No se pudo generar la imagen QR');
        }
        $filename = sprintf('%s_%s_%s.png', $prefix, Str::slug($content), Str::random(8));
        $path = 'qrcodes/' . $filename; // dentro del disco public
        Storage::disk('public')->put($path, $response->body());
        return Storage::url($path); // /storage/qrcodes/...
    }

    public function createWithRelations(array $data): Persona
    {
        return DB::transaction(function () use ($data) {
            // 🚀 OPTIMIZADO: Generar solo la RUTA del QR para la persona (sin descargar aún)
            $personaQrPath = null;
            $personaQrFilePath = null;
            $personaQrContent = null;
            
            if (!empty($data['documento'])) {
                // Formato correcto para el sistema QR: PERSONA_documento
                $personaQrContent = 'PERSONA_' . $data['documento'];
                $qrInfo = $this->generateQrPath($personaQrContent, 'persona');
                $personaQrPath = $qrInfo['url']; // /storage/qrcodes/persona_xxx.png
                $personaQrFilePath = $qrInfo['path']; // qrcodes/persona_xxx.png
            }

            // Crear persona con la ruta del QR (aunque el archivo físico no exista aún)
            $persona = Persona::create([
                'documento' => $data['documento'] ?? null,
                'Nombre' => $data['nombre'],
                'TipoPersona' => $data['tipoPersona'],
                'qrCode' => $personaQrPath, // Guardamos la ruta
                'correo' => $data['correo'] ?? null,
                'jornada_id' => $data['jornada_id'] ?? null,
                'programa_formacion_id' => $data['programa_formacion_id'] ?? null,
            ]);

            // 🔥 Despachar Job para generar el QR en segundo plano
            if ($personaQrFilePath && $personaQrContent) {
                GenerateQrCodeJob::dispatch(
                    'persona',
                    $persona->idPersona,
                    $personaQrContent,
                    $personaQrFilePath
                );
            }

            // Crear portátiles con QR en segundo plano
            if (!empty($data['portatiles']) && is_array($data['portatiles'])) {
                foreach ($data['portatiles'] as $p) {
                    $serial = $p['serial'] ?? '';
                    $qrPath = null;
                    $qrFilePath = null;
                    $qrContent = null;
                    
                    if ($serial) {
                        // Formato correcto para el sistema QR: PORTATIL_serial
                        $qrContent = 'PORTATIL_' . $serial;
                        $qrInfo = $this->generateQrPath($qrContent, 'portatil');
                        $qrPath = $qrInfo['url'];
                        $qrFilePath = $qrInfo['path'];
                    }
                    
                    $portatil = $persona->portatiles()->create([
                        'qrCode' => $qrPath, // Guardamos la ruta
                        'serial' => $serial,
                        'marca' => $p['marca'],
                        'modelo' => $p['modelo'],
                    ]);

                    // 🔥 Despachar Job para generar el QR del portátil en segundo plano
                    if ($qrFilePath && $qrContent) {
                        GenerateQrCodeJob::dispatch(
                            'portatil',
                            $portatil->portatil_id,
                            $qrContent,
                            $qrFilePath
                        );
                    }
                }
            }

            // Crear vehículos (sin QR)
            if (!empty($data['vehiculos']) && is_array($data['vehiculos'])) {
                foreach ($data['vehiculos'] as $v) {
                    $persona->vehiculos()->create([
                        'tipo' => $v['tipo'],
                        'placa' => $v['placa'],
                    ]);
                }
            }

            return $persona->load(['portatiles', 'vehiculos']);
        });
    }

    public function updateWithRelations(Persona $persona, array $data): Persona
    {
        return DB::transaction(function () use ($persona, $data) {
            $persona->update([
                'documento' => $data['documento'] ?? $persona->documento,
                'Nombre' => $data['nombre'] ?? $persona->Nombre,
                'TipoPersona' => $data['tipoPersona'] ?? $persona->TipoPersona,
                // Si viene documento, regeneramos QR; en caso contrario, conservamos el existente
                'qrCode' => isset($data['documento']) && $data['documento'] !== null
                    ? $this->storeQrPng($data['documento'], 'persona')
                    : $persona->qrCode,
                'correo' => $data['correo'] ?? $persona->correo,
                'jornada_id' => $data['jornada_id'] ?? $persona->jornada_id,
                'programa_formacion_id' => $data['programa_formacion_id'] ?? $persona->programa_formacion_id,
            ]);

            if (array_key_exists('portatiles', $data) && is_array($data['portatiles'])) {
                foreach ($data['portatiles'] as $p) {
                    if (!empty($p['id'])) {
                        $portatil = Portatiles::where('portatil_id', $p['id'])->where('persona_id', $persona->idPersona)->firstOrFail();
                        $portatil->update([
                            'qrCode' => isset($p['serial']) && $p['serial'] !== null
                                ? $this->storeQrPng($p['serial'], 'portatil')
                                : $portatil->qrCode,
                            'serial' => $p['serial'] ?? $portatil->serial,
                            'marca' => $p['marca'] ?? $portatil->marca,
                            'modelo' => $p['modelo'] ?? $portatil->modelo,
                        ]);
                    } else {
                        $qrPath = !empty($p['serial']) ? $this->storeQrPng($p['serial'], 'portatil') : null;
                        $persona->portatiles()->create([
                            'qrCode' => $qrPath,
                            'serial' => $p['serial'] ?? null,
                            'marca' => $p['marca'],
                            'modelo' => $p['modelo'],
                        ]);
                    }
                }
            }

            if (array_key_exists('vehiculos', $data) && is_array($data['vehiculos'])) {
                foreach ($data['vehiculos'] as $v) {
                    if (!empty($v['id'])) {
                        $vehiculo = Vehiculo::where('id', $v['id'])->where('persona_id', $persona->idPersona)->firstOrFail();
                        $vehiculo->update([
                            'tipo' => $v['tipo'] ?? $vehiculo->tipo,
                            'placa' => $v['placa'] ?? $vehiculo->placa,
                        ]);
                    } else {
                        $persona->vehiculos()->create([
                            'tipo' => $v['tipo'],
                            'placa' => $v['placa'],
                        ]);
                    }
                }
            }

            return $persona->load(['portatiles', 'vehiculos']);
        });
    }
}

