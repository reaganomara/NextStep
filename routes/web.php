<?php

use App\Http\Controllers\SafetyDemoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/safety-demo/{scenario}', [SafetyDemoController::class, 'run'])
    ->middleware('throttle:safety-demo')
    ->name('safety-demo.run');
