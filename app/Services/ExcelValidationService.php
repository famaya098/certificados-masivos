<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ExcelValidationService
{
    protected $requiredFields = [
        'nombre1', 'apellido1', 'dui', 'correo', 'telefono', 'departamento', 'distrito'
    ];

    public function validateRows(Collection $rows)
    {
        $validCount = 0;
        $invalidCount = 0;
        $errors = [];
        $duiList = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 porque la fila 1 es el encabezado y los índices empiezan en 0
            $isValid = true;
            $rowErrors = [];
            
            // Validar campos requeridos
            foreach ($this->requiredFields as $field) {
                if (!isset($row[$field]) || empty($row[$field])) {
                    $isValid = false;
                    $rowErrors[] = "Falta el campo '{$field}'";
                }
            }
            
            // Validar formato de DUI si existe
            if (isset($row['dui']) && !empty($row['dui'])) {
                $dui = $row['dui'];
                
                // Verificar duplicados
                if (in_array($dui, $duiList)) {
                    $isValid = false;
                    $rowErrors[] = "DUI duplicado en el archivo";
                } else {
                    $duiList[] = $dui;
                }
                
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
        }

        return [
            'valid_count' => $validCount,
            'invalid_count' => $invalidCount,
            'errors' => $errors,
            'total' => $rows->count()
        ];
    }
}