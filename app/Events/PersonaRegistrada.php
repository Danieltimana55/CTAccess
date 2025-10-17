<?php

namespace App\Events;

use App\Models\Persona;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Evento que se dispara cuando se registra una nueva persona.
 * Se emite por WebSocket para actualizar la vista en tiempo real.
 * Usa ShouldBroadcastNow para transmisión inmediata sin colas.
 */
class PersonaRegistrada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $persona;

    /**
     * Create a new event instance.
     */
    public function __construct(Persona $persona)
    {
        $this->persona = $persona;
        
        Log::info('📡 PersonaRegistrada evento creado', [
            'persona_id' => $persona->id,
            'nombre' => $persona->Nombre
        ]);
    }

    /**
     * Canal público para todos los usuarios
     */
    public function broadcastOn(): Channel
    {
        return new Channel('personas');
    }

    /**
     * Nombre del evento en el frontend
     */
    public function broadcastAs(): string
    {
        return 'persona.registrada';
    }

    /**
     * Datos que se envían al frontend
     */
    public function broadcastWith(): array
    {
        Log::info('📤 Enviando datos de persona registrada', [
            'persona_id' => $this->persona->id,
            'nombre' => $this->persona->Nombre,
            'tipo' => $this->persona->tipoPersona
        ]);

        return [
            'id' => $this->persona->id,
            'nombre' => $this->persona->Nombre,
            'documento' => $this->persona->documento ?? 'Sin documento',
            'tipo_persona' => $this->persona->tipoPersona,
            'correo' => $this->persona->correo,
            'timestamp' => now()->toIso8601String()
        ];
    }
}
