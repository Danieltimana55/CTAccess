<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Servicio centralizado para importación de datos desde CSV
 * Compatible con archivos exportados desde Excel, Google Sheets, etc.
 */
class ImportService
{
    private array $errors = [];
    private array $warnings = [];
    private int $successCount = 0;
    private int $errorCount = 0;

    /**
     * Importar datos desde archivo CSV
     *
     * @param UploadedFile $file Archivo CSV
     * @param array $columns Mapeo de columnas ['Header CSV' => 'campo_modelo']
     * @param array $rules Reglas de validación Laravel
     * @param callable $processRow Callback para procesar cada fila
     * @param array $options Opciones adicionales
     * @return array Resultado de la importación
     */
    public function importFromCsv(
        UploadedFile $file,
        array $columns,
        array $rules,
        callable $processRow,
        array $options = []
    ): array {
        $this->resetCounters();
        
        // Opciones por defecto
        $delimiter = $options['delimiter'] ?? $this->detectDelimiter($file);
        $skipFirstRow = $options['skip_first_row'] ?? true;
        $batchSize = $options['batch_size'] ?? 100;
        
        try {
            $handle = fopen($file->getRealPath(), 'r');
            
            if (!$handle) {
                throw new \RuntimeException('No se pudo abrir el archivo');
            }
            
            // Detectar y remover BOM si existe
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
                rewind($handle);
            }
            
            $rowNumber = 0;
            $headers = [];
            $batch = [];
            
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $rowNumber++;
                
                // Primera fila: encabezados
                if ($rowNumber === 1 && $skipFirstRow) {
                    $headers = $this->normalizeHeaders($row);
                    continue;
                }
                
                // Validar que la fila no esté vacía
                if ($this->isEmptyRow($row)) {
                    continue;
                }
                
                try {
                    // Mapear datos según columnas definidas
                    $data = $this->mapRowData($row, $headers, $columns);
                    
                    // Validar datos
                    $validator = Validator::make($data, $rules);
                    
                    if ($validator->fails()) {
                        $this->addError($rowNumber, $validator->errors()->first());
                        $this->errorCount++;
                        continue;
                    }
                    
                    // Agregar a lote
                    $batch[] = [
                        'data' => $data,
                        'row_number' => $rowNumber,
                    ];
                    
                    // Procesar lote cuando alcanza el tamaño configurado
                    if (count($batch) >= $batchSize) {
                        $this->processBatch($batch, $processRow);
                        $batch = [];
                    }
                    
                } catch (\Exception $e) {
                    $this->addError($rowNumber, $e->getMessage());
                    $this->errorCount++;
                }
            }
            
            // Procesar lote restante
            if (!empty($batch)) {
                $this->processBatch($batch, $processRow);
            }
            
            fclose($handle);
            
            return $this->getResult();
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage(),
                'errors' => $this->errors,
                'stats' => $this->getStats(),
            ];
        }
    }

    /**
     * Validar archivo antes de importar
     *
     * @param UploadedFile $file
     * @param array $requiredColumns
     * @param int $maxRows
     * @return array
     */
    public function validateFile(UploadedFile $file, array $requiredColumns = [], int $maxRows = 5000): array
    {
        $errors = [];
        
        // Validar extensión
        if (!in_array(strtolower($file->getClientOriginalExtension()), ['csv', 'txt'])) {
            $errors[] = 'El archivo debe ser CSV (.csv o .txt)';
        }
        
        // Validar tamaño (máx 10MB)
        if ($file->getSize() > 10485760) {
            $errors[] = 'El archivo no debe superar 10MB';
        }
        
        // Validar contenido
        try {
            $handle = fopen($file->getRealPath(), 'r');
            
            // Detectar y remover BOM
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
                rewind($handle);
            }
            
            $delimiter = $this->detectDelimiter($file);
            $headers = fgetcsv($handle, 0, $delimiter);
            
            if (!$headers) {
                $errors[] = 'El archivo está vacío o no tiene formato válido';
                fclose($handle);
                return ['valid' => false, 'errors' => $errors];
            }
            
            $headers = $this->normalizeHeaders($headers);
            
            // Validar columnas requeridas
            if (!empty($requiredColumns)) {
                $missingColumns = array_diff(
                    array_map('strtolower', $requiredColumns),
                    array_map('strtolower', $headers)
                );
                
                if (!empty($missingColumns)) {
                    $errors[] = 'Faltan columnas requeridas: ' . implode(', ', $missingColumns);
                }
            }
            
            // Contar filas
            $rowCount = 0;
            while (fgetcsv($handle, 0, $delimiter) !== false) {
                $rowCount++;
            }
            
            if ($rowCount > $maxRows) {
                $errors[] = "El archivo excede el máximo de {$maxRows} filas (encontradas: {$rowCount})";
            }
            
            fclose($handle);
            
        } catch (\Exception $e) {
            $errors[] = 'Error al leer el archivo: ' . $e->getMessage();
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'headers' => $headers ?? [],
            'rows' => $rowCount ?? 0,
        ];
    }

    /**
     * Detectar delimitador del CSV
     *
     * @param UploadedFile $file
     * @return string
     */
    private function detectDelimiter(UploadedFile $file): string
    {
        $handle = fopen($file->getRealPath(), 'r');
        $firstLine = fgets($handle);
        fclose($handle);
        
        $delimiters = [';' => 0, ',' => 0, '\t' => 0, '|' => 0];
        
        foreach ($delimiters as $delimiter => $count) {
            $delimiters[$delimiter] = substr_count($firstLine, $delimiter);
        }
        
        return array_search(max($delimiters), $delimiters);
    }

    /**
     * Normalizar encabezados
     *
     * @param array $headers
     * @return array
     */
    private function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header) {
            return trim(mb_strtolower($header));
        }, $headers);
    }

    /**
     * Verificar si una fila está vacía
     *
     * @param array $row
     * @return bool
     */
    private function isEmptyRow(array $row): bool
    {
        return empty(array_filter($row, function ($value) {
            return trim($value) !== '';
        }));
    }

    /**
     * Mapear datos de la fila según columnas definidas
     *
     * @param array $row
     * @param array $headers
     * @param array $columns
     * @return array
     */
    private function mapRowData(array $row, array $headers, array $columns): array
    {
        $data = [];
        
        foreach ($columns as $csvHeader => $fieldName) {
            $csvHeaderNormalized = mb_strtolower(trim($csvHeader));
            $index = array_search($csvHeaderNormalized, $headers);
            
            if ($index !== false && isset($row[$index])) {
                $value = trim($row[$index]);
                
                // Convertir valores especiales
                if (in_array(strtolower($value), ['si', 'sí', 'yes', '1', 'true'])) {
                    $value = true;
                } elseif (in_array(strtolower($value), ['no', '0', 'false', ''])) {
                    $value = false;
                }
                
                $data[$fieldName] = $value === '' ? null : $value;
            } else {
                $data[$fieldName] = null;
            }
        }
        
        return $data;
    }

    /**
     * Procesar lote de registros
     *
     * @param array $batch
     * @param callable $processRow
     * @return void
     */
    private function processBatch(array $batch, callable $processRow): void
    {
        foreach ($batch as $item) {
            try {
                $processRow($item['data']);
                $this->successCount++;
            } catch (\Exception $e) {
                $this->addError($item['row_number'], $e->getMessage());
                $this->errorCount++;
            }
        }
    }

    /**
     * Agregar error
     *
     * @param int $rowNumber
     * @param string $message
     * @return void
     */
    private function addError(int $rowNumber, string $message): void
    {
        $this->errors[] = [
            'row' => $rowNumber,
            'message' => $message,
        ];
    }

    /**
     * Agregar advertencia
     *
     * @param int $rowNumber
     * @param string $message
     * @return void
     */
    private function addWarning(int $rowNumber, string $message): void
    {
        $this->warnings[] = [
            'row' => $rowNumber,
            'message' => $message,
        ];
    }

    /**
     * Resetear contadores
     *
     * @return void
     */
    private function resetCounters(): void
    {
        $this->errors = [];
        $this->warnings = [];
        $this->successCount = 0;
        $this->errorCount = 0;
    }

    /**
     * Obtener estadísticas
     *
     * @return array
     */
    private function getStats(): array
    {
        return [
            'success' => $this->successCount,
            'errors' => $this->errorCount,
            'warnings' => count($this->warnings),
            'total' => $this->successCount + $this->errorCount,
        ];
    }

    /**
     * Obtener resultado de la importación
     *
     * @return array
     */
    private function getResult(): array
    {
        $success = $this->errorCount === 0;
        
        return [
            'success' => $success,
            'message' => $this->buildMessage(),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'stats' => $this->getStats(),
        ];
    }

    /**
     * Construir mensaje de resultado
     *
     * @return string
     */
    private function buildMessage(): string
    {
        if ($this->errorCount === 0) {
            return "Importación exitosa: {$this->successCount} registros procesados correctamente.";
        }
        
        return "Importación completada con errores: {$this->successCount} exitosos, {$this->errorCount} errores.";
    }
}
