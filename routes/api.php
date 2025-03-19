<?php

use App\Http\Controllers\ApproveRequest;
use App\Http\Controllers\UploadDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeneratesTbsReceiptController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/uploadDocument/', [UploadDocument::class, 'ActionUploadDocument'])->name('ActionUploadDocument');
Route::post('/plApprove/', [ApproveRequest::class, 'plApprove'])->name('plApprove');


Route::post('/generatesTbsReceipt', [GeneratesTbsReceiptController::class, 'generatesTbsReceipt'])->name('generatesTbsReceipt');