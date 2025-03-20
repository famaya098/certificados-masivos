<?php

use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\Controller;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UploadDocument;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\GetFirstUnusedController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,

    ]);
});

Route::post('/subirExcel', [ApplicantController::class, 'importarExcel'])->name('importarExcel');



Route::get('/get-scratchcard', [GetFirstUnusedController::class, 'getFirstUnusedScratchcard'])
    ->name('get.scratchcard');

Route::post('/validar-excel', [ApplicantController::class, 'validateExcel'])->name('validar.excel');
Route::get('/errores-validacion', [ApplicantController::class, 'getValidationErrors'])->name('validacion.errores');
Route::get('/descargar-reporte-errores', [ApplicantController::class, 'downloadErrorReport'])->name('descargar.errores');

require __DIR__ . '/auth.php';
