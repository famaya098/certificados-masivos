<?php

namespace App\Http\Services;

trait plUploadDocument
{

    /**
     * uploadDocument
     *
     * @return void
     */

    public function uploadDocument(array $request)
    {

        try {
            $filePath = $request['document'];
            if (!file_exists($filePath) || !is_readable($filePath)) {
                logger('Error: Archivo no existe o no tiene permisos de lectura', [$filePath]);
                return;
            }
            // Obtener el tipo MIME y el nombre del archivo
            $mimeType = mime_content_type($filePath);
            $fileName = basename($filePath);
            // Crear el archivo CURLFile correctamente
            $document = new \CURLFile($filePath, $mimeType, $fileName);
            $postFields = [
                'document' => $document,
                'type' => $request['type'],
            ];

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.uanataca.com/api/v1/requests/' . $request['create_request_id'] . '/pl_upload_document/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $postFields,
                CURLOPT_SSLCERT => storage_path('ra_certs/cer.pem'),
                CURLOPT_SSLKEY => storage_path('ra_certs/key.pem'),
                CURLOPT_HTTPHEADER => [],
            ]);
            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                throw new \Exception(curl_error($curl));
            }
            curl_close($curl);
            $response = json_decode($response);
        } catch (\Exception $e) {
            logger('$e', [$e]);
            logger('$errorMessage', [$e->getMessage()]);
        }
    }

}
