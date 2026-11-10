<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CatalogController;

Route::get('/v1/health', fn () => response()->json([
    'status' => 'ok',
    'application' => 'BuildLab API',
    'version' => 'v1',
]))->name('api.v1.health');

Route::get('/v1/catalog/characters', [CatalogController::class, 'characters'])
    ->name('api.v1.catalog.characters');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
