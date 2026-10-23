<?php

use App\Http\Controllers\AiDemoController;
use App\Http\Controllers\GuidedDemoController;
use App\Http\Controllers\SafetyDemoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/safety-demo/{scenario}', [SafetyDemoController::class, 'run'])
    ->middleware('throttle:safety-demo')
    ->name('safety-demo.run');

Route::post('/guided-demo/{scenario}', [GuidedDemoController::class, 'run'])
    ->middleware('throttle:guided-demo')
    ->name('guided-demo.run');

Route::post('/ai-demo/{scenario}', [AiDemoController::class, 'run'])
    ->middleware('throttle:ai-demo')
    ->name('ai-demo.run');
