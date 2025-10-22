<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Servicio centralizado para exportación de datos a CSV
 * Compatible con Excel, Google Sheets y cualquier software de hojas de cálculo
 */
class ExportService
{
    /**
     * Exportar datos a CSV
     *
     * @param Collection $data Datos a exportar
     * @param array $columns Columnas a incluir ['header' => 'campo']
     * @param string $filename Nombre del archivo
     * @return StreamedResponse
     */
    public function exportToCsv(Collection $data, array $columns, string $filename): StreamedResponse
    {
        $filename = $this->sanitizeFilename($filename);
        
        return new StreamedResponse(function () use ($data, $columns) {
            $handle = fopen('php://output', 'w');
            
            // BOM para UTF-8 (compatibilidad con Excel)
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Escribir encabezados
            fputcsv($handle, array_keys($columns), ';');
            
            // Escribir datos
            foreach ($data as $row) {
                $rowData = [];
                foreach ($columns as $field) {
                    $rowData[] = $this->formatValue($row, $field);
                }
                fputcsv($handle, $rowData, ';');
            }
            
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Exportar template vacío para importación
     *
     * @param array $columns Columnas del template
     * @param string $filename Nombre del archivo
     * @param array $exampleRow Fila de ejemplo (opcional)
     * @return StreamedResponse
     */
    public function exportTemplate(array $columns, string $filename, array $exampleRow = []): StreamedResponse
    {
        $filename = $this->sanitizeFilename($filename);
        
        return new StreamedResponse(function () use ($columns, $exampleRow) {
            $handle = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Escribir encabezados
            fputcsv($handle, array_keys($columns), ';');
            
            // Escribir fila de ejemplo si existe
            if (!empty($exampleRow)) {
                fputcsv($handle, array_values($exampleRow), ';');
            }
            
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Formatear valor para exportación
     *
     * @param mixed $row
     * @param string $field
     * @return string
     */
    private function formatValue($row, string $field): string
    {
        // Acceder a propiedades anidadas (ej: "programa.codigo")
        $keys = explode('.', $field);
        $value = $row;
        
        foreach ($keys as $key) {
            if (is_object($value)) {
                $value = $value->{$key} ?? null;
            } elseif (is_array($value)) {
                $value = $value[$key] ?? null;
            } else {
                $value = null;
                break;
            }
        }
        
        // Formatear según tipo
        if ($value === null) {
            return '';
        }
        
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        
        if (is_bool($value)) {
            return $value ? 'Sí' : 'No';
        }
        
        if (is_array($value)) {
            return implode(', ', $value);
        }
        
        // Limpiar saltos de línea y caracteres especiales
        return trim(preg_replace('/[\r\n]+/', ' ', (string) $value));
    }

    /**
     * Sanitizar nombre de archivo
     *
     * @param string $filename
     * @return string
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remover extensión si existe
        $filename = preg_replace('/\.[^.]+$/', '', $filename);
        
        // Reemplazar caracteres especiales
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
        
        // Agregar timestamp y extensión
        return $filename . '_' . now()->format('Y-m-d_His') . '.csv';
    }
}
