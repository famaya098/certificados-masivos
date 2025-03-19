<?php

namespace App\Http\Controllers;

use App\Imports\ApplicantImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ApplicantController extends Controller
{
    public function importarExcel(Request $request)
    {
        $importar = new ApplicantImport();

        Excel::import($importar, $request->archivo);
    }
}
