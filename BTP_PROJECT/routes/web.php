<?php

use App\Http\Controllers\CollectorController;
use App\Http\Controllers\DevisEstimatifReUseController;
use App\Http\Controllers\NiveauBatimentController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\OrganisationRoleController;
use App\Http\Controllers\PermissionCrontroller;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
<<<<<<< HEAD
use App\Http\Controllers\TemplateEstimatifController;
=======
use App\Http\Controllers\TemplateDevisEstimatifController;
>>>>>>> dev_emmanuel
use App\Http\Controllers\TemplateEstimatifQte;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ArrondissementController;
use App\Http\Controllers\BatimentController;
use App\Http\Controllers\BordereauImportController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CollectionPrixController;
use App\Http\Controllers\CommuneController;
use App\Http\Controllers\CorpsEtatController;
use App\Http\Controllers\DeviseController;
use App\Http\Controllers\DevisEstimatifController;
use App\Http\Controllers\DevisEstimatifQuantitatifController;
use App\Http\Controllers\MateriauController;
use App\Http\Controllers\OrganisationUserController;
use App\Http\Controllers\PriceStatisticsController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\UniteMesureController;
use App\Models\Commune;
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

    // middleware  verification d'organisation actif

    Route::middleware(['organisation.active'])->group(function () {



        Route::resource('organisations/roles', OrganisationRoleController::class)->names('organisations.roles');

        Route::resource('organisations/users', OrganisationUserController::class)
            ->names('organisations.users');

        Route::resource('organisations', OrganisationController::class)->names('organisations')->only(['show', 'edit', 'update', 'destroy']);


        //  Projets
        Route::resource('projets', ProjetController::class);

        //  Clients
        Route::resource('clients', ClientController::class);

        //  Projets
        Route::resource('projets', ProjetController::class);

        //  Bâtiments
        Route::resource('batiments', BatimentController::class);

        //  Niveaux
        Route::resource('niveaux-batiment', NiveauBatimentController::class)->parameters(['niveaux-batiment' => 'niveauBatiment']);
        ;



        Route::get(
            'projets/{projet}/batiments',
            [BatimentController::class, 'indexByProjet']
        )->name('projets.batiments.index');

        Route::get(
            'projets/{projet}/batiments/create',
            [BatimentController::class, 'createFromProjet']
        )->name('projets.batiments.create');



        // Devis estimatif
        Route::resource('batiments.devisestimatif', DevisEstimatifController::class);


        Route::post(
            '/batiments/{batiment}/devis/{devis}/valider',
            [DevisEstimatifController::class, 'valider']
        )->name('devisestimatif.valider');

        Route::post(
            '/batiments/{batiment}/devis/{devis}/brouillon',
            [DevisEstimatifController::class, 'brouillon']
        )->name('devisestimatif.brouillon');

        Route::post('/batiments/{batiment}/devis/{devis}/send', [DevisEstimatifController::class, 'sendToClient'])
            ->name('devisestimatif.send');

        // routes/web.php
        Route::get('/batiments/{batiment}/devis/{devis}/pdf', [DevisEstimatifController::class, 'downloadPdf'])
            ->name('devisestimatif.pdf');

        // Routes pour la réutilisation de devis
        Route::get('/devis-estimatif/{devis}/reuse', [DevisEstimatifController::class, 'reuse'])
            ->name('batiments.devisestimatif.reuse');

        Route::post('/devis-estimatif/{devis}/reuse', [DevisEstimatifController::class, 'storeReuse'])
            ->name('batiments.devisestimatif.reuse.store');

        Route::get('/api/projets/{projet}/batiments-disponibles', [DevisEstimatifController::class, 'getBatimentsDisponibles'])
            ->name('api.projets.batiments-disponibles');



<<<<<<< HEAD
        Route::get('/templates-estimatif', [TemplateEstimatifController::class, 'index'])
            ->name('templates.index');

        Route::get('/templates/search', [TemplateEstimatifController::class, 'search'])
            ->name('templates.search');

        // 2. Routes avec paramètres {template}
        Route::get('/templates/{template}', [TemplateEstimatifController::class, 'show'])
            ->name('templates.show');

        Route::get('/templates/{template}/reuse', [TemplateEstimatifController::class, 'reuse'])
            ->name('templates.reuse');

        Route::post('/templates/{template}/reuse', [TemplateEstimatifController::class, 'storeReuse'])
=======
        Route::get('/templates-estimatif', [TemplateDevisEstimatifController::class, 'index'])
            ->name('templates.index');

        Route::get('/templates/search', [TemplateDevisEstimatifController::class, 'search'])
            ->name('templates.search');

        // 2. Routes avec paramètres {template}
        Route::get('/templates/{template}', [TemplateDevisEstimatifController::class, 'show'])
            ->name('templates.show');

        Route::get('/templates/{template}/reuse', [TemplateDevisEstimatifController::class, 'reuse'])
            ->name('templates.reuse');

        Route::post('/templates/{template}/reuse', [TemplateDevisEstimatifController::class, 'storeReuse'])
>>>>>>> dev_emmanuel
            ->name('templates.reuse.store');

        // 3. API pour charger les bâtiments
        Route::get('/api/projets/{projet}/batiments-disponibles', [DevisEstimatifController::class, 'getBatimentsDisponibles'])
            ->name('api.projets.batiments-disponibles');

        // OU mieux encore, groupez les routes:
        Route::prefix('templates')->name('templates.')->group(function () {
<<<<<<< HEAD
            Route::get('/', [TemplateEstimatifController::class, 'index'])->name('index');
            Route::get('/search', [TemplateEstimatifController::class, 'search'])->name('search');
            Route::get('/{template}', [TemplateEstimatifController::class, 'show'])->name('show');
            Route::get('/{template}/reuse', [TemplateEstimatifController::class, 'reuse'])->name('reuse');
            Route::post('/{template}/reuse', [TemplateEstimatifController::class, 'storeReuse'])->name('reuse.store');
=======
            Route::get('/', [TemplateDevisEstimatifController::class, 'index'])->name('index');
            Route::get('/search', [TemplateDevisEstimatifController::class, 'search'])->name('search');
            Route::get('/{template}', [TemplateDevisEstimatifController::class, 'show'])->name('show');
            Route::get('/{template}/reuse', [TemplateDevisEstimatifController::class, 'reuse'])->name('reuse');
            Route::post('/{template}/reuse', [TemplateDevisEstimatifController::class, 'storeReuse'])->name('reuse.store');

            Route::post('/{template}/validate', [TemplateDevisEstimatifController::class, 'validateTemplate'])
                ->name('validate');

            Route::post('/{template}/unvalidate', [TemplateDevisEstimatifController::class, 'unvalidateTemplate'])
                ->name('unvalidate');
>>>>>>> dev_emmanuel
        });

        Route::prefix('api')->name('api.')->group(function () {
            Route::get('/projets/{projet}/batiments-disponibles', [DevisEstimatifController::class, 'getBatimentsDisponibles'])
                ->name('projets.batiments-disponibles');
        });

        Route::resource('collectors', CollectorController::class)
            ->only(['index', 'create', 'store', 'destroy']);
    });


    //devis estimatif quantitatif

    Route::prefix('batiments/{batiment}')
        ->name('batiments.')
        ->group(function () {

            Route::get(
                'devis-estimatif-quantitatif/reuse-form',
                [DevisEstimatifQuantitatifController::class, 'reuseForm']
            )->name('devis-estimatif-quantitatif.reuse-form');

            Route::post(
                'devis-estimatif-quantitatif/{devis}/reuse',
                [DevisEstimatifQuantitatifController::class, 'reuse']
            )->name('devis-estimatif-quantitatif.reuse');

            Route::get(
                'devis-estimatif-quantitatif/{devis_estimatif_quantitatif}/pdf',
                [DevisEstimatifQuantitatifController::class, 'downloadPdf']
            )->name('devis-estimatif-quantitatif.pdf');


            // CRUD via resource
            Route::resource('devis-estimatif-quantitatif', DevisEstimatifQuantitatifController::class);

            Route::get(
                'devis-quantitatif/{devis_estimatif_quantitatif}/corps-etat',
                [DevisEstimatifQuantitatifController::class, 'editCorpsEtat']
            )->name('devis-estimatif-quantitatif.editCorpsEtat');

            Route::put(
                'devis-quantitatif/{devis_estimatif_quantitatif}/corps-etat',
                [DevisEstimatifQuantitatifController::class, 'updateCorpsEtat']
            )->name('devis-estimatif-quantitatif.updateCorpsEtat');

            // Actions métier
            Route::post(
                'devis-estimatif-quantitatif/{devis_estimatif_quantitatif}/valider',
                [DevisEstimatifQuantitatifController::class, 'valider']
            )->name('devis-estimatif-quantitatif.valider');

            Route::post(
                'devis-estimatif-quantitatif/{devis_estimatif_quantitatif}/brouillon',
                [DevisEstimatifQuantitatifController::class, 'brouillon']
            )->name('devis-estimatif-quantitatif.brouillon');


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
    Route::resource('unites-mesure', UniteMesureController::class)->parameters(['unites-mesure' => 'uniteMesure']);

    // Matériaux
    Route::resource('materiaux', MateriauController::class)->parameters(['materiaux' => 'materiau']);

    // Devises
    Route::resource('devises', DeviseController::class);

    // Corps d'état
    Route::resource('corps-etat', CorpsEtatController::class)->parameters(['corps-etat' => 'corpsEtat']);

    //collections-prix
    // Route pour les arrondissements - DOIT être avant resource
    Route::get('collections-prix/arrondissements/{communeId}', [CollectionPrixController::class, 'getArrondissements'])
        ->name('collections-prix.arrondissements');

    // Route de validation - DOIT être avant resource
    Route::post('collections-prix/{id}/validate', [CollectionPrixController::class, 'validateCollection'])
        ->name('collections-prix.validate');

    // Resource routes - DOIT être en dernier
    Route::resource('collections-prix', CollectionPrixController::class);


    Route::get('/stats/collections-prix', [PriceStatisticsController::class, 'index'])
    ->name('stats.collections-prix');

    Route::post('/filter', [PriceStatisticsController::class, 'filter'])->name('stats.filter');


    Route::prefix('templates-estimatif-qte')
        ->name('templates-estimatif-qte.')
        ->group(function () {

            // Liste des templates
            Route::get('/', [TemplateEstimatifQte::class, 'index'])
                ->name('index');

            // Afficher un template (utilise la même vue que le show normal)
            Route::get('/{template}', [TemplateEstimatifQte::class, 'show'])
                ->name('show');
        });
});

require __DIR__ . '/auth.php';
