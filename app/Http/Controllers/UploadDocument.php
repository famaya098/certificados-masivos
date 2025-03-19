<?php

namespace App\Http\Controllers;

use App\Http\Services\plUploadDocument;
use App\Models\error_operation;
use Illuminate\Http\Request;

class UploadDocument extends Controller
{
    use plUploadDocument;

    public function ActionUploadDocument(Request $request)
    {
        $request->validate([
            'create_request_id' => 'required',
            'dui' => 'required',
        ]);
        try {
            $errorOperation = new error_operation();
            $types = ["document_owner", "document_front", "document_rear"];
            $allowedExtensions = ['png', 'jpg', 'jpeg', 'pdf'];
            $idDocument = $request->input('dui');
            foreach ($types as $type) {
                $fileFound = false;
                foreach ($allowedExtensions as $extension) {
                    $filePath = storage_path("app/public/{$type}/{$idDocument}.{$extension}");
                    if (file_exists($filePath)) {
                        $fileFound = true;
                        break;
                    }
                }

                if (!$fileFound) {
                    $errorOperation->operation = 'uploadDocument';
                    $errorOperation->message = "El documento: $idDocument No se encuentra.";
                    $errorOperation->save();
                    return response()->json(['error' => "El documento: $idDocument No se encuentra."], 400);
                }

                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                if (!in_array($extension, $allowedExtensions)) {
                    $errorOperation->operation = 'uploadDocument';
                    $errorOperation->message = "El documento: $idDocument tiene una extensión no permitida.";
                    $errorOperation->save();

                    return response()->json(['error' => "El documento: $idDocument tiene una extensión no permitida."], 400);
                }
                $data = [
                    'type' => $type,
                    'document' => $filePath,
                    'create_request_id' => $request->input('create_request_id'),
                ];

                $this->uploadDocument($data);
            }

            return response()->json(['message' => 'Documentos cargados exitosamente']);

        } catch (\Exception $th) {
            $errorOperation->operation = 'uploadDocument';
            $errorOperation->message = "Ocurrió un error al cargar los documentos: " . $th->getMessage();
            $errorOperation->save();

            return response()->json(['message' => 'Ocurrió un error al cargar los documentos'], 500);
        }

    }
}
