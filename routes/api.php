<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Models\Acceso;
/**
 * API pública para accesos recientes (usado en Home con WebSockets)
 */
Route::get('/accesos/recientes', function () {
    try {
        $accesos = Acceso::with('persona')
            ->whereDate('fecha_entrada', today())
            ->latest('fecha_entrada')
            ->limit(10)
            ->get()
            ->map(function ($acceso) {
                return [
                    'id' => $acceso->id,
                    'persona' => $acceso->persona->Nombre,
                    'documento' => $acceso->persona->documento,
                    'tipo' => $acceso->fecha_salida ? 'salida' : 'entrada',
                    'tiempo' => $acceso->fecha_salida 
                        ? $acceso->fecha_salida->toIso8601String()
                        : $acceso->fecha_entrada->toIso8601String()
                ];
            });

        return response()->json($accesos);
    } catch (\Exception $e) {
        Log::error('Error en /api/accesos/recientes: ' . $e->getMessage());
        return response()->json(['error' => 'Error al obtener accesos'], 500);
    }
});

/**
 * API para datos de gráficos (Dashboard Analytics)
 */
Route::get('/analytics/charts', function () {
    try {
        // 📊 Gráfico 1: Accesos por hora del día (últimas 24h)
        $accesosPorHora = [];
        for ($hora = 0; $hora < 24; $hora++) {
            $count = Acceso::whereDate('fecha_entrada', today())
                ->whereRaw('HOUR(fecha_entrada) = ?', [$hora])
                ->count();
            $accesosPorHora[] = $count;
        }

        // 📊 Gráfico 2: Comparativa últimos 7 días
        $ultimosSieteDias = [];
        $labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = today()->subDays($i);
            $entradas = Acceso::whereDate('fecha_entrada', $fecha)->count();
            $salidas = Acceso::whereDate('fecha_salida', $fecha)
                ->whereNotNull('fecha_salida')
                ->count();
            
            $ultimosSieteDias['entradas'][] = $entradas;
            $ultimosSieteDias['salidas'][] = $salidas;
            $labels[] = $fecha->locale('es')->isoFormat('ddd DD');
        }

        // 📊 Gráfico 3: Distribución Entradas/Salidas hoy
        $entradasHoy = Acceso::whereDate('fecha_entrada', today())->count();
        $salidasHoy = Acceso::whereDate('fecha_salida', today())
            ->whereNotNull('fecha_salida')
            ->count();
        $dentroAhora = Acceso::whereDate('fecha_entrada', today())
            ->whereNull('fecha_salida')
            ->count();

        // 📊 Gráfico 4: Tendencia del mes (accesos diarios)
        $diasDelMes = [];
        $accesosPorDia = [];
        $primerDiaMes = today()->startOfMonth();
        $ultimoDiaMes = today()->endOfMonth();
        
        for ($fecha = $primerDiaMes->copy(); $fecha <= $ultimoDiaMes; $fecha->addDay()) {
            if ($fecha > today()) break;
            
            $count = Acceso::whereDate('fecha_entrada', $fecha)->count();
            $diasDelMes[] = $fecha->format('d');
            $accesosPorDia[] = $count;
        }

        return response()->json([
            'accesos_por_hora' => [
                'labels' => array_map(fn($h) => sprintf('%02d:00', $h), range(0, 23)),
                'data' => $accesosPorHora
            ],
            'ultimos_siete_dias' => [
                'labels' => $labels,
                'entradas' => $ultimosSieteDias['entradas'],
                'salidas' => $ultimosSieteDias['salidas']
            ],
            'distribucion_hoy' => [
                'labels' => ['Entradas', 'Salidas', 'Dentro Ahora'],
                'data' => [$entradasHoy, $salidasHoy, $dentroAhora]
            ],
            'tendencia_mes' => [
                'labels' => $diasDelMes,
                'data' => $accesosPorDia
            ],
            'timestamp' => now()->toIso8601String()
        ]);
    } catch (\Exception $e) {
        Log::error('Error en /api/analytics/charts: ' . $e->getMessage());
        return response()->json(['error' => 'Error al obtener datos de gráficos'], 500);
    }
});

/**
 * API para registros recientes de personas (usado en Home con WebSockets)
 */
Route::get('/personas/recientes', function () {
    try {
        $personas = App\Models\Persona::latest('created_at')
            ->limit(15)
            ->get()
            ->map(function ($persona) {
                return [
                    'id' => $persona->id,
                    'nombre' => $persona->Nombre,
                    'documento' => $persona->documento ?? 'Sin documento',
                    'tipo_persona' => $persona->tipoPersona,
                    'correo' => $persona->correo,
                    'tiempo' => $persona->created_at->toIso8601String()
                ];
            });

        return response()->json($personas);
    } catch (\Exception $e) {
        Log::error('Error en /api/personas/recientes: ' . $e->getMessage());
        return response()->json(['error' => 'Error al obtener personas recientes'], 500);
    }
});

