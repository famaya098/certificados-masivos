<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ValidateExcelCommand extends Command
{
    protected $signature = 'uanataca:validate-excel {file : Path to Excel file}';
    protected $description = 'Validate Excel file without processing API calls';

    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("El archivo no existe: {$filePath}");
            return 1;
        }
        
        $this->info("Validando archivo: {$filePath}");
        
        try {
            // Cargar el Excel en memoria
            $collection = Excel::toCollection(new class implements ToCollection, WithHeadingRow {
                public function collection(Collection $rows) {
                    return $rows;
                }
            }, $filePath);
            
            $rows = $collection->first();
            
            $this->info("Total de registros: " . $rows->count());
            
            $validCount = 0;
            $invalidCount = 0;
            $errors = [];
            
            $requiredFields = [
                'nombre1', 'apellido1', 'dui', 'correo', 'telefono', 'departamento', 'distrito'
            ];
            
            // Crear una barra de progreso
            $bar = $this->output->createProgressBar($rows->count());
            $bar->start();
            
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 porque la fila 1 es el encabezado y los índices empiezan en 0
                $isValid = true;
                $rowErrors = [];
                
                // Validar campos requeridos
                foreach ($requiredFields as $field) {
                    if (!isset($row[$field]) || empty($row[$field])) {
                        $isValid = false;
                        $rowErrors[] = "Falta el campo '{$field}'";
                    }
                }
                
                // Validar formato de DUI si existe
                if (isset($row['dui']) && !empty($row['dui'])) {
                    $dui = $row['dui'];
                    
                    // Verificar que sea numérico y tenga la longitud correcta (9 dígitos para El Salvador)
                    if (!is_numeric($dui) || strlen($dui) != 9) {
                        $isValid = false;
                        $rowErrors[] = "El DUI debe tener 9 dígitos numéricos";
                    } else {
                        // Validar archivos de imágenes
                        $types = ["document_owner", "document_front", "document_rear"];
                        $allowedExtensions = ['png', 'jpg', 'jpeg', 'pdf'];
                        $missingFiles = [];
                        
                        foreach ($types as $type) {
                            $fileFound = false;
                            foreach ($allowedExtensions as $extension) {
                                $filePath = storage_path("app/public/{$type}/{$dui}.{$extension}");
                                if (file_exists($filePath)) {
                                    $fileFound = true;
                                    break;
                                }
                            }
                            
                            if (!$fileFound) {
                                $isValid = false;
                                $missingFiles[] = $type;
                            }
                        }
                        
                        if (!empty($missingFiles)) {
                            $rowErrors[] = "Faltan archivos de imagen: " . implode(', ', $missingFiles);
                        }
                    }
                }
                
                if ($isValid) {
                    $validCount++;
                } else {
                    $invalidCount++;
                    $errors[] = [
                        'row' => $rowNumber,
                        'dui' => $row['dui'] ?? 'N/A',
                        'errors' => $rowErrors
                    ];
                }
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine(2);
            
            $this->info("Resultados de validación:");
            $this->info("- Registros válidos: {$validCount}");
            $this->info("- Registros con errores: {$invalidCount}");
            
            if ($invalidCount > 0) {
                $this->warn("Detalles de errores:");
                foreach ($errors as $error) {
                    $this->error("Fila {$error['row']} (DUI: {$error['dui']}): " . implode(", ", $error['errors']));
                }
                
                // Preguntar si desea guardar los errores en un archivo
                if ($this->confirm('¿Desea guardar los errores en un archivo?')) {
                    $outputFile = 'validation_errors_' . date('Y-m-d_H-i-s') . '.txt';
                    $content = "Errores de validación - " . date('Y-m-d H:i:s') . "\n\n";
                    
                    foreach ($errors as $error) {
                        $content .= "Fila {$error['row']} (DUI: {$error['dui']}): " . implode(", ", $error['errors']) . "\n";
                    }
                    
                    file_put_contents($outputFile, $content);
                    $this->info("Errores guardados en: {$outputFile}");
                }
            } else {
                $this->info("¡Todos los registros son válidos!");
            }
            
            return $invalidCount > 0 ? 1 : 0;
            
        } catch (\Exception $e) {
            $this->error("Error al validar el archivo: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}