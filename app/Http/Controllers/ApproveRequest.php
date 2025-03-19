<?php

namespace App\Http\Controllers;

use App\Models\applicant_data;
use App\Models\error_operation;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Psr7Request;

class ApproveRequest extends Controller
{
    public function plApprove($request)
    {
        try {
            $create_request_id = $request['create_request_id'];
            $client = new Client([
                'cert' => storage_path('ra_certs/cer.pem'),
                'ssl_key' => storage_path('ra_certs/key.pem'),
            ]);

            $headers = [
                'Content-Type' => 'application/json',
            ];
            $body = [
                'username' => env('API_USERNAME', 'default_username'),
                'password' => env('API_PASSWORD', 'default_password'),
                'pin' => env('API_PIN', 'default_pin'),
                'rao_id' => env('API_RAO_ID', 449),
                'language' => env('API_LANGUAGE', 'ES'),
            ];
            $body = json_encode($body);
            $request = new Psr7Request('POST', "https://api.sandbox.uanataca.com/api/v1/requests/{$create_request_id}/pl_approve/", $headers, $body);
            $res = $client->sendAsync($request)->wait();
            $applicant_model = new applicant_data();
            $applicant_model->pl_approve_json = $res->getBody();
            $applicant_model->save();
            return response()->json(['message' => "Usuario {$create_request_id} aprobado exitosamente."]);

        } catch (\Throwable $th) {
            //throw $th;
            $errorOperation = new error_operation();
            $errorOperation->operation = 'uploadDocument';
            $errorOperation->message = "Ocurrió un error al cargar los documentos: " . $th->getMessage();
            $errorOperation->save();

        }

    }
}
