<?php

use App\Http\Controllers\BordereauImportController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;





Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});





Route::get('/home', function () {
    return Inertia::render('Home');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');



    Route::get('bordereaux/import', [BordereauImportController::class, 'create'])
        ->name('bordereaux.import');

    Route::post('bordereaux/import', [BordereauImportController::class, 'store'])
        ->name('bordereaux.store');

    Route::delete('bordereaux/ligne/{ligne}', [BordereauImportController::class, 'destroyLigne'])
        ->name('bordereaux.destroyLigne');

    Route::resource('bordereaux', BordereauImportController::class)
        ->except(['create', 'store']);
});

require __DIR__ . '/auth.php';
