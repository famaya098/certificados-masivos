<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Support\Facades\Log;
use App\Models\ApplicantData;
use App\Models\RaData;

class GetFirstUnusedController extends Controller
{
    public function getFirstUnusedScratchcard()
    {
        try {
            // ra_id desde la base de datos
            $raData = RaData::where('status', 1)->first();
            
            if (!$raData) {
                throw new \Exception('No se encontró ningún RA activo en la base de datos');
            }
            
            $raId = $raData->ra_id;
            
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
                'ra' => $raId,
            ];
            $body = json_encode($bodyArray);

            // URL completa
            $url = 'https://api.uanataca.com/api/v1/scratchcards/get_first_unused/';
            
            // Crear la solicitud
            $request = new Psr7Request('GET', $url, $headers, $body);
            
            // Realizar la solicitud y esperar la respuesta
            $res = $client->sendAsync($request)->wait();
            
            // Obtener el cuerpo de la respuesta como string
            $responseBody = (string) $res->getBody();
            
            // Registrar la respuesta completa en el log para depuración
            Log::info('Respuesta completa de get_first_unused:', [
                'status' => $res->getStatusCode(),
                'body' => $responseBody
            ]);
            
            // Crear un nuevo registro en la base de datos
            $applicant = ApplicantData::create([
                'get_first_unused_json' => $responseBody,
                'get_first_unused_status' => 1, // 
                'overall_status' => 'PROCESANDO'
            ]);
            
            // Devolver la respuesta como JSON 
            $data = [
                'success' => true,
                'data' => json_decode($responseBody),
                'applicant_id' => $applicant->id
            ];
            return $data;
            
        } catch (\Exception $e) {
            // Manejar el error y registrarlo
            Log::error('Error en getFirstUnusedScratchcard:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Crear un registro con error
            $applicant = ApplicantData::create([
                'get_first_unused_status' => 2, // 
                'get_first_unused_error' => $e->getMessage(),
                'overall_status' => 'ERROR'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener scratchcard: ' . $e->getMessage(),
                'applicant_id' => $applicant->id
            ], 500);
        }
    }
}