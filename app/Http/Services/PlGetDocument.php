<?php

namespace App\Http\Services;

use App\Models\ApplicantData;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Psr7Request;

trait PlGetDocument
{
    public function getDocument($pk, $applicant_id)
    {
        $client = new Client([
            'cert' => storage_path('ra_certs/cer.pem'),
            'ssl_key' => storage_path('ra_certs/key.pem'),
            'verify' => false,
        ]);

        $headers = [
            'Content-Type' => 'application/json',
        ];

        $body = [
            'type' => 'contract',
            'rao_id' => '449',
        ];
        $body = json_encode($body);

        $request = new Psr7Request('POST', "https://api.sandbox.uanataca.com/api/v1/requests/{$pk}/pl_get_document/", $headers, $body);
        $res = $client->sendAsync($request)->wait(); // Convertir la respuesta a JSON
        $response = (string) $res->getBody(); //Convertir la respuesta a STRING.
        $responsePaso5 = json_decode($response);

        $applicant = ApplicantData::where('id', $applicant_id)->update([
            'pl_get_document_text' => $responsePaso5[0]->document,
            'pl_get_document_status' => 1,
        ]);

    }
}
