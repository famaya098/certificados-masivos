<?php

namespace App\Http\Controllers;
use App\Models\ApplicantData;
use App\Models\rao_data;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Support\Facades\Log;

class GeneratesTbsReceiptController extends Controller
{
    public function generatesTbsReceipt(Request $request)
    {
        try {
            // Validar la solicitud
            $request->validate([
                'create_request_id' => 'required',
            ]);
            
            $createRequestId = $request->input('create_request_id');

            // Obtener el rao_id activo desde la base de datos
            $raoData = rao_data::where('status', 1)->first();
            
            if (!$raoData) {
                throw new \Exception('No se encontró ningún RAO activo en la base de datos');
            }
            
            $raoId = $raoData->rao_id;
            
            // cliente HTTP con los certificados
            $client = new Client([
                'cert' => storage_path('ra_certs/cer.pem'),
                'ssl_key' => storage_path('ra_certs/key.pem'),
                'verify' => false, // true en producción
            ]);
            
            $headers = [
                'Content-Type' => 'application/json',
            ];

            $bodyArray = [
                'rao' => $raoId,
                'type' => 'APPROVE',
            ];
            $body = json_encode($bodyArray);

            // URL completa
            $url = "https://api.sandbox.uanataca.com/api/v1/requests/{$createRequestId}/generates_tbs_receipt/";
            
            // Crear la solicitud
            $request = new Psr7Request('POST', $url, $headers, $body);
            
            // Realizar la solicitud y esperar la respuesta
            $res = $client->sendAsync($request)->wait();
            
            // Obtener el cuerpo de la respuesta como string
            $responseBody = (string) $res->getBody();
            
            // Registrar la respuesta completa en el log para depuración
            Log::info('Respuesta completa de generates_tbs_receipt:', [
                'status' => $res->getStatusCode(),
                'body' => $responseBody
            ]);
            
            // Buscar el registro del solicitante por create_request_pk
            $applicant = ApplicantData::where('create_request_pk', $createRequestId)->first();
            
            if (!$applicant) {
                throw new \Exception("No se encontró el registro para la solicitud con ID: {$createRequestId}");
            }
            
            // Actualizar el registro en la base de datos
            $applicant->generates_tbs_receipt_json = $responseBody;
            $applicant->generates_tbs_receipt_status = 1; // Completado
            $applicant->save();
            
            // Devolver la respuesta como JSON 
            return response()->json([
                'success' => true,
                'message' => 'Declaración RAO generada exitosamente',
                'data' => json_decode($responseBody)
            ]);
            
        } catch (\Exception $e) {
            // Manejar el error y registrarlo
            Log::error('Error en generatesTbsReceipt:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Si existe el registro, actualizarlo con el error
            if (isset($createRequestId)) {
                $applicant = ApplicantData::where('create_request_pk', $createRequestId)->first();
                if ($applicant) {
                    $applicant->generates_tbs_receipt_status = 2; // Error
                    $applicant->generates_tbs_receipt_error = $e->getMessage();
                    $applicant->save();
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Error al generar la declaración RAO: ' . $e->getMessage()
            ], 500);
        }
    }
}