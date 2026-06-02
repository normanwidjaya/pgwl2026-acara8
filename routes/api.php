<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// GeoJSON API
Route::get('/points', [ApiController::class, 'geojson_points'])->name('geojson.points');
Route::get('/point/{id}', [ApiController::class, 'geojson_point'])->name('geojson.point');
Route::get('/polylines', [ApiController::class, 'geojson_polylines'])->name('geojson.polylines');
Route::get('/polygons', [ApiController::class, 'geojson_polygons'])->name('geojson.polygons');
