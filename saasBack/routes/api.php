<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegistroController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/registro', [RegistroController::class, 'store'])->middleware('throttle:registro');
Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
