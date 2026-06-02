<?php

use App\Http\Controllers\PolygonsController;
use App\Http\Controllers\PolylinesController;
use App\Http\Controllers\PointsController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
   //  return view('welcome');
// })->name('home');

Route::get('/', [PageController::class, 'landingpage'])->name('home');

Route::get('/peta', [PageController::class, 'peta'])->middleware(['auth', 'verified'])->name('peta');

Route::get('/tabel', [PageController::class, 'tabel'])->name('tabel');

// Points
Route::post('/store-points', [PointsController::class, 'store'])->name('points.store');
Route::get('/points/{id}', [PointsController::class, 'show'])->name('points.show');
Route::get('/points/{id}/edit', [PointsController::class, 'edit'])->name('points.edit');
Route::put('/points/{id}', [PointsController::class, 'update'])->name('points.update');
Route::patch('/points/{id}', [PointsController::class, 'update'])->name('points.update');

// Polylines
Route::post('/store-polylines', [PolylinesController::class, 'store'])->name('polylines.store');
Route::get('/polylines/{id}', [PolylinesController::class, 'show'])->name('polylines.show');
Route::get('/polylines/{id}/edit', [PolylinesController::class, 'edit'])->name('polylines.edit');
Route::put('/polylines/{id}', [PolylinesController::class, 'update'])->name('polylines.update');
Route::patch('/polylines/{id}', [PolylinesController::class, 'update'])->name('polylines.update');

// Polygons
Route::post('/store-polygons', [PolygonsController::class, 'store'])->name('polygons.store');
Route::get('/polygons/{id}', [PolygonsController::class, 'show'])->name('polygons.show');
Route::get('/polygons/{id}/edit', [PolygonsController::class, 'edit'])->name('polygons.edit');
Route::put('/polygons/{id}', [PolygonsController::class, 'update'])->name('polygons.update');
Route::patch('/polygons/{id}', [PolygonsController::class, 'update'])->name('polygons.update');

// Delete resources
Route::delete('/delete-point/{id}', [PointsController::class, 'destroy'])->name('points.destroy');
Route::delete('/delete-polyline/{id}', [PolylinesController::class, 'destroy'])->name('polylines.destroy');
Route::delete('/delete-polygon/{id}', [PolygonsController::class, 'destroy'])->name('polygons.destroy');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/settings.php';
