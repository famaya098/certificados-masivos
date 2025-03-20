<?php

namespace App\Imports;

use App\Http\Controllers\ApproveRequest;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Http\Controllers\GetFirstUnusedController;
use App\Http\Services\PlGetDocument;
use App\Http\Controllers\UploadDocument;
use App\Http\Controllers\GeneratesTbsReceiptController;
use App\Models\ApplicantData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ApplicantImport implements WithHeadingRow, ToCollection, WithChunkReading
{
    use PlGetDocument;

    public function chunkSize(): int
    {
        return 500; // Procesa en lotes de 500
    }

    public function collection(Collection $collection)
    {
        $client = new Client([
            'cert' => storage_path('ra_certs/cer.pem'),
            'ssl_key' => storage_path('ra_certs/key.pem'),
        ]);

        $headers = [
            'Content-Type' => 'application/json',
        ];
        
        foreach ($collection as $key => $value) {
            try {
                // PASO 1: Obtener Scratchcard
                $controlador = new GetFirstUnusedController();
                $respuestaPaso1 = $controlador->getFirstUnusedScratchcard();
                
                if (!isset($respuestaPaso1['data']) || !isset($respuestaPaso1['data']->sn)) {
                    Log::error('Error en Paso 1: No se obtuvo una respuesta válida', [$respuestaPaso1]);
                    continue; // Saltar a la siguiente fila si hay error
                }
                
                $paso1_SN = $respuestaPaso1['data']->sn;
                $paso1_RA = $respuestaPaso1['data']->registration_authority;
                $paso1_ApplicantID = $respuestaPaso1['applicant_id'];

                // PASO 2: Crear solicitud
                $data = [
                    'secure_element' => '2',
                    'profile' => 'PFnubeQBCRCiudadano',
                    'validity_time' => '365',
                    'scratchcard' => $paso1_SN,
                    'given_name' => $value['nombre1'],
                    'second_name' => $value['nombre2'] ?? '',
                    'country_name' => 'SV',
                    'serial_number' => $value['dui'],
                    'id_document_country' => 'SV',
                    'id_document_type' => 'IDC',
                    'surname_1' => $value['apellido1'],
                    'surname_2' => $value['apellido2'] ?? '',
                    'registration_authority' => $paso1_RA,
                    'email' => $value['correo'],
                    'mobile_phone_number' => $value['telefono'],
                    'residence_address' => $value['departamento'],
                    'residence_city' => $value['distrito'],
                    'residence_postal_code' => '05010',
                    'paperless_mode' => 1,
                ];
                
                $body = json_encode($data);
                
                $request = new Psr7Request('POST', 'https://api.sandbox.uanataca.com/api/v1/requests/', $headers, $body);
                $res = $client->sendAsync($request)->wait();
                $response = (string) $res->getBody();
                $responsePaso2 = json_decode($response);

                if (!isset($responsePaso2->pk)) {
                    Log::error('Error en Paso 2: No se obtuvo una respuesta válida', [$response]);
                    continue; // siguiente fila si hay error
                }

                Log::info('RESPUESTA2', [$responsePaso2->pk]);
                
                // Actualizar registro del paso 2
                ApplicantData::where('id', $paso1_ApplicantID)->update([
                    'create_request_pk' => $responsePaso2->pk,
                    'create_request_status' => 1,
                    'overall_status' => 'PROCESANDO'
                ]);

                // PASO 3: Subir documentos
                $uploadDocumentController = new UploadDocument();
                $uploadRequest = new Request([
                    'create_request_id' => $responsePaso2->pk,
                    'dui' => $value['dui'],
                ]);
                
                $uploadResponse = $uploadDocumentController->ActionUploadDocument($uploadRequest);
                Log::info('Respuesta de uploadDocument', [$uploadResponse]);
                
                // Verificar si el paso 3 fue exitoso antes de continuar
                if (!is_object($uploadResponse) || !isset($uploadResponse->original) || !isset($uploadResponse->original['message']) || $uploadResponse->original['message'] !== 'Documentos cargados exitosamente') {
                    Log::error('Error en Paso 3: Error al cargar documentos', [$uploadResponse]);
                    
                    // Actualizar el estado en la base de datos
                    ApplicantData::where('id', $paso1_ApplicantID)->update([
                        'pl_upload_document_status' => 2, // Error
                        'pl_upload_document_error' => 'Error al cargar documentos',
                        'overall_status' => 'ERROR'
                    ]);
                    
                    continue; // siguiente fila
                }
                
                // Actualizar el estado en la base de datos para el paso 3
                ApplicantData::where('id', $paso1_ApplicantID)->update([
                    'pl_upload_document_status' => 1, // Éxito
                ]);

                // PASO 4: Generar declaración RAO
                $generatesTbsReceiptController = new GeneratesTbsReceiptController();
                $tbsRequest = new Request([
                    'create_request_id' => $responsePaso2->pk,
                ]);
                
                $tbsResponse = $generatesTbsReceiptController->generatesTbsReceipt($tbsRequest);
                Log::info('Respuesta de generatesTbsReceipt', [$tbsResponse]);
                
                // Verificar si el paso 4 fue exitoso
                if (!is_object($tbsResponse) || !isset($tbsResponse->original) || !isset($tbsResponse->original['success']) || $tbsResponse->original['success'] !== true) {
                    Log::error('Error en Paso 4: Error al generar declaración RAO', [$tbsResponse]);
                    continue; // siguiente fila
                }
                
                // Si todo salió bien, actualizar el estado general
                ApplicantData::where('id', $paso1_ApplicantID)->update([
                    'overall_status' => 'COMPLETADO'
                ]);
                
                Log::info('Proceso completado exitosamente para DUI: ' . $value['dui']);

                // PASO 5: OBTENER DOCUMENTO (BASE 64)
                $this->getDocument($responsePaso2->pk, $paso1_ApplicantID);

                // PASO 6: APROVACIÓN
                $aprovacion = new ApproveRequest();
                $aprovacion->plApprove($responsePaso2->pk);
                
            } catch (\Exception $e) {
                Log::error('Error en el proceso de importación', [
                    'mensaje' => $e->getMessage(),
                    'fila' => $key + 2, // +2 porque la fila 1 es el encabezado y los índices empiezan en 0
                    'dui' => $value['dui'] ?? 'No disponible'
                ]);
            }
        }
    }

    public function headingRow(): int
    {
        return 1;
    }
}