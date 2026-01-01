<?php

use App\Http\Controllers\DietTypesController;
use App\Http\Controllers\EatersController;
use App\Http\Controllers\IngredientsController;
use App\Http\Controllers\MealtimesController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/ingredients', [IngredientsController::class, 'index'])->name('ingredients.index');
    Route::get('/ingredients/json-list', [IngredientsController::class, 'getJsonList'])->name('ingredients.json_list');
    Route::post('/ingredients/save', [IngredientsController::class, 'save'])->name('ingredients.save');
    Route::post('/ingredients/delete', [IngredientsController::class, 'delete'])->name('ingredients.delete');

    Route::get('/diet-types', [DietTypesController::class, 'index'])->name('diet_types.index');
    Route::get('/diet-types/json-list', [DietTypesController::class, 'getJsonList'])->name('diet_types.json_list');
    Route::post('/diet-types/save', [DietTypesController::class, 'save'])->name('diet_types.save');
    Route::post('/diet-types/delete', [DietTypesController::class, 'delete'])->name('diet_types.delete');

    Route::get('/mealtimes', [MealtimesController::class, 'index'])->name('mealtimes.index');
    Route::get('/mealtimes/json-list', [MealtimesController::class, 'getJsonList'])->name('mealtimes.json_list');
    Route::post('/mealtimes/save', [MealtimesController::class, 'save'])->name('mealtimes.save');
    Route::post('/mealtimes/delete', [MealtimesController::class, 'delete'])->name('mealtimes.delete');

    Route::get('/eaters', [EatersController::class, 'index'])->name('eaters.index');
    Route::get('/eaters/json-list', [EatersController::class, 'getJsonList'])->name('eaters.json_list');
    Route::post('/eaters/save', [EatersController::class, 'save'])->name('eaters.save');
    Route::post('/eaters/delete', [EatersController::class, 'delete'])->name('eaters.delete');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
