<?php

namespace App\Http\Controllers;

use App\Imports\ApplicantImport;
use App\Services\ExcelValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class ApplicantController extends Controller
{
    protected $validationService;

    public function __construct(ExcelValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    public function validateExcel(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('archivo');
        $path = $file->store('temp');
        
        try {
            // Cargar el Excel en memoria
            $collection = Excel::toCollection(new class implements ToCollection, WithHeadingRow {
                public function collection(Collection $rows) {
                    return $rows;
                }
            }, Storage::path($path));
            
            $rows = $collection->first();
            
            // Validar los datos
            $validationResults = $this->validationService->validateRows($rows);
            
            // Guardar el archivo si es válido para usarlo posteriormente
            if ($validationResults['invalid_count'] == 0) {
                $savedPath = $file->store('validated');
                session(['validated_excel' => $savedPath]);
            } else {
                // Guardar los errores en la sesión para mostrarlos
                session(['validation_errors' => $validationResults['errors']]);
            }
            
            return response()->json([
                'success' => true,
                'results' => $validationResults,
                'can_process' => $validationResults['invalid_count'] == 0
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al validar el archivo: ' . $e->getMessage()
            ], 500);
        } finally {
            // Eliminar el archivo temporal
            Storage::delete($path);
        }
    }

    public function importarExcel(Request $request)
    {
        // Verificar si hay un archivo validado
        $validatedPath = session('validated_excel');
        
        if (!$validatedPath || !Storage::exists($validatedPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Debe validar el archivo primero'
            ], 400);
        }
        
        try {
            $importar = new ApplicantImport();
            Excel::import($importar, Storage::path($validatedPath));
            
            // Limpiar la sesión
            session()->forget('validated_excel');
            
            return response()->json([
                'success' => true,
                'message' => 'Archivo procesado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getValidationErrors()
    {
        $errors = session('validation_errors', []);
        return response()->json(['errors' => $errors]);
    }
}