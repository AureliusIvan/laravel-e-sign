<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SignatureController;

Route::get('/proposal/convert/{filename}', [SignatureController::class, 'convertPdfToImages'])
    ->name('proposal.convert');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
