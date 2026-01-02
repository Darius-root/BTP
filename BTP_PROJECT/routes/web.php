<?php

use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\PermissionCrontroller;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
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




Route::middleware(['auth', 'verified'])->group(function () {
    // Tableau de bord
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Profil utilisateur
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rôles and permissions & système
    Route::resource('roles', RoleController::class);

    Route::get('/permissions', [PermissionCrontroller::class, 'index'])->name('roles.permissions.index');

    // Utilisateurs
    Route::resource('users', UserController::class)
        ->only(['index', 'show']);







    Route::post('/organisations/{organisation}/activate', [
        OrganisationController::class,
        'activate'
    ])->name('organisations.activate');
     Route::post('/organisations/{organisation}/deactivate', [
        OrganisationController::class,
        'deactivate'
    ])->name('organisations.deactivate');

    Route::get('/organisations', [OrganisationController::class, 'index'])->name('organisations.index');

    Route::middleware(['organisation.active'])->group(function () {

        Route::get('/organisations/add-user', [OrganisationController::class, 'addUserToOrganisation'])->name('organisations.addUser');

        Route::post('/organisations/store-user', [OrganisationController::class, 'storeUserToOrganisation'])->name('organisations.storeUser');

        // Organisations

        Route::get('/permissions', [PermissionCrontroller::class, 'index'])->name('roles.permissions.index');
    });
});


require __DIR__ . '/auth.php';
