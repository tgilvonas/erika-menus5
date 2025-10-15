<?php

use App\Http\Controllers\IngredientsController;
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
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
