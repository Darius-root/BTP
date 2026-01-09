<?php

use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\OrganisationRoleController;
use App\Http\Controllers\PermissionCrontroller;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ArrondissementController;
use App\Http\Controllers\BatimentController;
use App\Http\Controllers\BordereauImportController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CollectionPrixController;
use App\Http\Controllers\CommuneController;
use App\Http\Controllers\CorpsEtatController;
use App\Http\Controllers\DeviseController;
use App\Http\Controllers\MateriauController;
use App\Http\Controllers\OrganisationUserController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\UniteMesureController;
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




Route::middleware(['auth', 'verified', 'organisation.profil'])->group(function () {
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
    //collections-prix  
    Route::resource('collections-prix', CollectionPrixController::class);
    Route::get('collections-prix/arrondissements/{communeId}', [CollectionPrixController::class, 'getArrondissements'])
        ->name('collections-prix.arrondissements');






    Route::post('/organisations/{organisation}/activate', [
        OrganisationController::class,
        'activate'
    ])->name('organisations.activate');
    Route::post('/organisations/{organisation}/deactivate', [
        OrganisationController::class,
        'deactivate'
    ])->name('organisations.deactivate');


    Route::get('/organisations', [OrganisationController::class, 'index'])->name('organisations.index');

    Route::resource('organisations', OrganisationController::class)->names('organisations')->only(['index', 'create', 'store']);

    Route::middleware(['organisation.active'])->group(function () {
        Route::resource('organisations/roles', OrganisationRoleController::class)->names('organisations.roles');
        Route::resource('organisations/users', OrganisationUserController::class)
            ->names('organisations.users');

        Route::resource('organisations', OrganisationController::class)->names('organisations')->only(['show', 'edit', 'update', 'destroy']);
        //Roles organisationnels

        //Utilisateurs dans une organisation


        //  Projets
        Route::resource('projets', ProjetController::class);



        // Organisations

        //  Organisations

        //  Clients
        Route::resource('clients', ClientController::class);

        //  Projets
        Route::resource('projets', ProjetController::class);

        //  Bâtiments
        Route::resource('batiments', BatimentController::class);

        Route::get(
            'projets/{projet}/batiments',
            [BatimentController::class, 'indexByProjet']
        )->name('projets.batiments.index');

        Route::get(
            'projets/{projet}/batiments/create',
            [BatimentController::class, 'createFromProjet']
        )->name('projets.batiments.create');
    });




    //bordereaux
    Route::prefix('bordereaux')->name('bordereaux.')->group(function () {


        Route::get('/', [BordereauImportController::class, 'index'])
            ->name('index');

        Route::get('/import', [BordereauImportController::class, 'create'])
            ->name('create');

        Route::post('/import', [BordereauImportController::class, 'store'])
            ->name('store');

        Route::get('/{bordereau}', [BordereauImportController::class, 'show'])
            ->name('show');

        Route::get('/{bordereau}/edit', [BordereauImportController::class, 'edit'])
            ->name('edit');

        Route::put('/{bordereau}', [BordereauImportController::class, 'update'])
            ->name('update');

        Route::delete('/{bordereau}', [BordereauImportController::class, 'destroy'])
            ->name('destroy');

        Route::delete('/{bordereau}/lignes/{designation}', [BordereauImportController::class, 'destroyDesignation'])
            ->name('designation.destroy');

        Route::put('/{id}/toggle-status', [BordereauImportController::class, 'toggleStatus'])->name('bordereaux.toggle-status');
    });

    // Communes
    Route::resource('communes', CommuneController::class);
    // Dans routes/web.php
    Route::post('/communes/preview-code', [CommuneController::class, 'previewCode'])
        ->name('communes.preview-code');

    // Arrondissements
    Route::resource('arrondissements', ArrondissementController::class);

    // Unités de mesure
    Route::resource('unites-mesure', UniteMesureController::class);

    // Matériaux
    Route::resource('materiaux', MateriauController::class)->parameters(['materiaux' => 'materiau']);

    // Devises
    Route::resource('devises', DeviseController::class);

    // Corps d'état
    Route::resource('corps-etat', CorpsEtatController::class)->parameters(['corps-etat' => 'corpsEtat']);


});


require __DIR__ . '/auth.php';
