<?php

namespace App\Imports;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Http\Controllers\GetFirstUnusedController;

class ApplicantImport implements WithHeadingRow, ToCollection
{
    public function collection(Collection $collection)
    {
        $client = new Client([
            'cert' => storage_path('ra_certs/cer.pem'),
            'ssl_key' => storage_path('ra_certs/key.pem'),
            'verify' => false,
        ]);

        $headers = [
            'Content-Type' => 'application/json',
        ];

        $controlador = new GetFirstUnusedController();
        $controlador->getFirstUnusedScratchcard();
        logger('PASO1', [$controlador->getFirstUnusedScratchcard()]);

        /* foreach ($collection as $key => $value) {
            $data = [
                'secure_element' => '2',
                'profile' => 'PFnubeQBCRCiudadano',
                'validity_time' => '365',
                'scratchcard' => '1575883',
                'given_name' => $value['nombre1'],
                'second_name' => $value['nombre2'],
                'country_name' => 'SV',
                'serial_number' => $value['dui'],
                'id_document_country' => 'SV',
                'id_document_type' => 'IDC',
                'surname_1' => $value['apellido1'],
                'surname_2' => $value['apellido2'],
                'registration_authority' => '610',
                'email' => $value['correo'],
                'mobile_phone_number' => $value['telefono'],
                'residence_address' => $value['departamento'],
                'residence_city' => $value['distrito'],
                'residence_postal_code' => '05010',
                'paperless_mode' => 1,
            ];
            $body = json_encode($data);
            logger('ENVIO', [$body]);
            
            $request = new Psr7Request('POST', 'https://api.sandbox.uanataca.com/api/v1/requests/', $headers, $body);
            $res = $client->sendAsync($request)->wait(); // Convertir la respuesta a JSON
            $response = (string) $res->getBody(); //Convertir la respuesta a STRING.
            
            $response = json_decode($response); // Convertir la respuesta a JSON
            //return $response->sn; //Acceder al valor de Scratchcard
            logger('RESPUESTA', [$res]);
            logger('RESPUESTA_String', [$response]);
            logger('RESPUESTA_String', [$response->sn]);


        } */
    }

    public function headingRow(): int
    {
        return 1;
    }
}
