<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Infrastructure\Http\Controllers\HealthController;
use Infrastructure\Http\Controllers\RecursoController;
use Infrastructure\Http\Controllers\GrupoController;

Route::get('/health', [HealthController::class, 'index']);
Route::get('/health/info', [HealthController::class, 'info']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::prefix('v1')->group(function () {
    Route::post('recursos/gerar-descricao', [RecursoController::class, 'gerarDescricao']);
    Route::post('recursos/gerar-tags', [RecursoController::class, 'gerarTags']);
    Route::apiResource('recursos', RecursoController::class);
    
    Route::post('grupos/{id}/sync-recursos', [GrupoController::class, 'syncRecursos']);
    Route::apiResource('grupos', GrupoController::class);
});
