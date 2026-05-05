<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    ListingController,
    SimulationController,
    MetadataController,
    StatsController,
};

Route::get('/', DashboardController::class)->name('dashboard');

// Achats
Route::get('/achats', [ListingController::class, 'indexAchats'])->name('achats.index');
Route::get('/achats/create', [ListingController::class, 'createAchat'])->name('achats.create');
Route::post('/achats', [ListingController::class, 'store'])->name('achats.store');
Route::get('/achats/{listing}', [ListingController::class, 'showAchat'])->name('achats.show');
Route::get('/achats/{listing}/edit', [ListingController::class, 'editAchat'])->name('achats.edit');
Route::put('/achats/{listing}', [ListingController::class, 'update'])->name('achats.update');
Route::delete('/achats/{listing}', [ListingController::class, 'destroy'])->name('achats.destroy');

// Locations
Route::get('/locations', [ListingController::class, 'indexLocations'])->name('locations.index');
Route::get('/locations/create', [ListingController::class, 'createLocation'])->name('locations.create');
Route::post('/locations', [ListingController::class, 'store'])->name('locations.store');
Route::get('/locations/{listing}/edit', [ListingController::class, 'editLocation'])->name('locations.edit');
Route::put('/locations/{listing}', [ListingController::class, 'update'])->name('locations.update');
Route::delete('/locations/{listing}', [ListingController::class, 'destroy'])->name('locations.destroy');

// Simulations
Route::get('/simulations', [SimulationController::class, 'index'])->name('simulations.index');
Route::get('/simulations/create', [SimulationController::class, 'create'])->name('simulations.create');
Route::post('/simulations', [SimulationController::class, 'store'])->name('simulations.store');
Route::delete('/simulations/{simulation}', [SimulationController::class, 'destroy'])->name('simulations.destroy');

// Stats
Route::get('/stats', StatsController::class)->name('stats');

// API JSON (appelée depuis Vue via axios, sans rechargement de page)
Route::prefix('api')->name('api.')->group(function () {
    Route::post('/metadata', MetadataController::class)->name('metadata');
    Route::post('/simulations/calculer', [SimulationController::class, 'calculer'])->name('simulations.calculer');
    Route::get('/locations/ville/{ville}', [ListingController::class, 'parVille'])->name('locations.ville');
    Route::get('/stats/villes', [StatsController::class, 'parVille'])->name('stats.villes');
});
