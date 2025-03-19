<?php

namespace App\Imports;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Http\Controllers\GetFirstUnusedController;
use App\Models\ApplicantData;

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
        
        foreach ($collection as $key => $value) {
            
            $controlador = new GetFirstUnusedController(); // Instanciamos el controlador
            $respuestaPaso1 = $controlador->getFirstUnusedScratchcard(); // Mandamos a llamar la función del controlador instanciado
            $paso1_SN = $respuestaPaso1['data']->sn; // Obtenemos el Número Serial del paso1
            $paso1_RA = $respuestaPaso1['data']->registration_authority; // Obtenemos el Registro de Autorización
            $paso1_ApplicantID = $respuestaPaso1['applicant_id']; // obtenemos el ID del registro en la base de datos (tabla Applicant_Data)

            $data = [
                'secure_element' => '2',
                'profile' => 'PFnubeQBCRCiudadano',
                'validity_time' => '365',
                'scratchcard' => $paso1_SN,
                'given_name' => $value['nombre1'],
                'second_name' => $value['nombre2'],
                'country_name' => 'SV',
                'serial_number' => $value['dui'],
                'id_document_country' => 'SV',
                'id_document_type' => 'IDC',
                'surname_1' => $value['apellido1'],
                'surname_2' => $value['apellido2'],
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
            $res = $client->sendAsync($request)->wait(); // Convertir la respuesta a JSON
            $response = (string) $res->getBody(); //Convertir la respuesta a STRING.
            $responsePaso2 = json_decode($response);

            logger('RESPUESTA2', [$responsePaso2->pk]);
            logger('RESPUESTA2', [$res]);
            logger('RESPUESTA2_String', [json_decode($response)]);
            
            $applicant = ApplicantData::where('id', $paso1_ApplicantID)->update([
                'create_request_pk' => $responsePaso2->pk,
                'create_request_status' => 1,
            ]);
        }
    }

    public function headingRow(): int
    {
        return 1;
    }
}
