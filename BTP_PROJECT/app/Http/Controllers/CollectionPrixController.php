<?php

namespace App\Http\Controllers;

use App\Models\CollectionPrix;
use App\Models\Commune;
use App\Models\Arrondissement;
use App\Models\Materiau;
use App\Models\Devise;
use App\Models\CorpsEtat;
use App\Services\OrganisationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Throwable;

class CollectionPrixController extends Controller
{
    /**
     * Liste des collectes
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $isAdmin = $user->hasRole('SYSTEM_ADMIN_PLATEFORME');
            $activeOrg = getPermissionsTeamId();

            // Vérification des permissions
            if (!$isAdmin && !OrganisationContext::hasPermission($user, $activeOrg, 'ORG_COLLECTION_VIEW')) {
                return back()->with('error', "Vous n'avez pas la permission de voir les collections de prix.");
            }

            $search = $request->input('search');
            $columns = $request->input('columns', []);
            $allowed = ['id', 'description_materiaux', 'price', 'commune_id', 'categorie_id', 'created_at'];
            $columns = array_intersect($columns, $allowed);

            $query = CollectionPrix::with([
                'commune',
                'arrondissement',
                'materiau.unite',
                'devise',
                'categorie',
                'user',
                'validator',
            ]);

            if (!$isAdmin) {
                $query->where('user_id', $user->id);
            }

            // Filtres
            if ($request->filled('commune_id')) {
                $query->where('commune_id', $request->commune_id);
            }
            if ($request->filled('categorie_id')) {
                $query->where('categorie_id', $request->categorie_id);
            }

            // Recherche globale
            if ($search && count($columns)) {
                $query->where(function ($q) use ($search, $columns) {
                    foreach ($columns as $column) {
                        if ($column === 'created_at') {
                            $q->orWhereDate($column, $search);
                        } else {
                            $q->orWhere($column, 'like', "%{$search}%");
                        }
                    }
                });
            }

            // Pagination
            $collections = $query->latest()->paginate(5)->withQueryString();

            return Inertia::render('CollectionsPrix/Index', [
                'collections' => [
                    'data' => $collections->items(),
                    'links' => $collections->links()->elements[0] ?? [], // Liens de pagination
                    'current_page' => $collections->currentPage(),
                    'last_page' => $collections->lastPage(),
                    'per_page' => $collections->perPage(),
                    'total' => $collections->total(),
                    'from' => $collections->firstItem(),
                    'to' => $collections->lastItem(),
                ],
                'communes' => Commune::orderBy('libelle')->get(),
                'categories' => CorpsEtat::orderBy('intitule')->get(),
                'filters' => $request->only(['commune_id', 'categorie_id']),
                'activeOrganisation' => $activeOrg,
                'isAdmin' => $isAdmin,
            ]);
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    /**
     * Formulaire de création
     */
    public function create()
    {
        try {
            $user = Auth::user();
            $activeOrg = getPermissionsTeamId();
            $isAdmin = $user->hasRole('SYSTEM_ADMIN_PLATEFORME');

            if (!$isAdmin && !OrganisationContext::hasPermission($user, $activeOrg, 'ORG_COLLECTION_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer une collecte.");
            }

            return Inertia::render('CollectionsPrix/Create', [
                'communes' => Commune::orderBy('libelle')->get(),
                'materiaux' => Materiau::with('unite')->orderBy('nom')->get(),
                'devises' => Devise::orderBy('libelle')->get(),
                'categories' => CorpsEtat::orderBy('intitule')->get(),
                'activeOrganisation' => $activeOrg,
            ]);

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Détails d’une collecte
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $isAdmin = $user->hasRole('SYSTEM_ADMIN_PLATEFORME');
            $activeOrg = getPermissionsTeamId();

            $collection = CollectionPrix::with([
                'commune',
                'arrondissement',
                'materiau.unite',
                'devise',
                'categorie',
                'user',
                'validator',
            ])->findOrFail($id);

            if (
                !$isAdmin && !OrganisationContext::hasPermission($user, $activeOrg, 'ORG_COLLECTION_VIEW') &&
                $collection->user_id !== $user->id
            ) {
                return back()->with('error', "Vous n'avez pas la permission de voir cette collecte.");
            }

            return Inertia::render('CollectionsPrix/Show', [
                'collection' => $collection,
                'activeOrganisation' => $activeOrg,
                'isAdmin' => $isAdmin,
            ]);

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Enregistrement d’une collecte (toujours NON validée)
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $activeOrg = getPermissionsTeamId();
            $isAdmin = $user->hasRole('SYSTEM_ADMIN_PLATEFORME');

            if (!$isAdmin && !OrganisationContext::hasPermission($user, $activeOrg, 'ORG_COLLECTION_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer une collecte.");
            }

            $validated = $request->validate([
                'commune_id' => 'required|exists:communes,id',
                'arrondissement_id' => 'nullable|exists:arrondissements,id',
                'quartier_id' => 'nullable|string|max:255',
                'materiau_id' => 'required|exists:materiaux,id',
                'devise_id' => 'required|exists:devises,id',
                'categorie_id' => 'required|exists:corps_etat,id',
                'description_materiaux' => 'required|string',
                'detail' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'point_vente' => 'nullable|string|max:255',
            ]);

            CollectionPrix::create([
                ...$validated,
                'user_id' => $user->id,
                'organisation_id' => $activeOrg,
                'is_validated' => false,
            ]);

            return redirect()->route('collections-prix.index')
                ->with('success', "Collecte enregistrée et envoyée pour validation.");

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Formulaire d’édition
     */
    public function edit($id)
    {
        try {
            $user = Auth::user();
            $activeOrg = getPermissionsTeamId();
            $isAdmin = $user->hasRole('SYSTEM_ADMIN_PLATEFORME');

            $collection = CollectionPrix::findOrFail($id);

            if (
                !$isAdmin && (!OrganisationContext::hasPermission($user, $activeOrg, 'ORG_COLLECTION_EDIT') ||
                    $collection->user_id !== $user->id || $collection->is_validated)
            ) {
                return back()->with('error', "Vous n'avez pas la permission de modifier cette collecte.");
            }

            return Inertia::render('CollectionsPrix/Edit', [
                'collection' => $collection->load([
                    'commune',
                    'arrondissement',
                    'materiau.unite',
                    'devise',
                    'categorie',
                ]),
                'communes' => Commune::orderBy('libelle')->get(),
                'materiaux' => Materiau::with('unite')->orderBy('nom')->get(),
                'devises' => Devise::orderBy('libelle')->get(),
                'categories' => CorpsEtat::orderBy('intitule')->get(),
                'activeOrganisation' => $activeOrg,
            ]);

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            $activeOrg = getPermissionsTeamId();
            $isAdmin = $user->hasRole('SYSTEM_ADMIN_PLATEFORME');

            $collection = CollectionPrix::findOrFail($id);

            if (
                !$isAdmin && (!OrganisationContext::hasPermission($user, $activeOrg, 'ORG_COLLECTION_EDIT') ||
                    $collection->user_id !== $user->id || $collection->is_validated)
            ) {
                return back()->with('error', "Vous n'avez pas la permission de modifier cette collecte.");
            }

            $validated = $request->validate([
                'commune_id' => 'required|exists:communes,id',
                'arrondissement_id' => 'nullable|exists:arrondissements,id',
                'quartier_id' => 'nullable|string|max:255',
                'materiau_id' => 'required|exists:materiaux,id',
                'devise_id' => 'required|exists:devises,id',
                'categorie_id' => 'required|exists:corps_etat,id',
                'description_materiaux' => 'required|string',
                'detail' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'point_vente' => 'nullable|string|max:255',
            ]);

            $collection->update($validated);

            return redirect()->route('collections-prix.index')
                ->with('success', "Collecte mise à jour avec succès.");

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Validation d’une collecte — ADMIN uniquement
     */
    public function validateCollection($id)
    {
        try {
            $user = Auth::user();
            $isAdmin = $user->hasRole('SYSTEM_ADMIN_PLATEFORME');

            if (!$isAdmin) {
                return back()->with('error', "Vous n'avez pas la permission de valider cette collecte.");
            }

            $collection = CollectionPrix::findOrFail($id);

            $collection->update([
                'is_validated' => true,
                'validated_by' => $user->id,
                'validated_at' => now(),
            ]);

            return back()->with('success', "Collecte validée avec succès.");

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Suppression d’une collecte
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $activeOrg = getPermissionsTeamId();
            $isAdmin = $user->hasRole('SYSTEM_ADMIN_PLATEFORME');

            $collection = CollectionPrix::findOrFail($id);

            if (!$isAdmin && !OrganisationContext::hasPermission($user, $activeOrg, 'ORG_COLLECTION_DELETE')) {
                return back()->with('error', "Vous n'avez pas la permission de supprimer cette collecte.");
            }

            if ($collection->is_validated) {
                return back()->with('error', "Une collecte validée ne peut pas être supprimée.");
            }

            $collection->delete();

            return redirect()->route('collections-prix.index')
                ->with('success', "Collecte supprimée avec succès.");

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Arrondissements par commune (AJAX)
     */
    public function getArrondissements($communeId)
    {
        return Arrondissement::where('commune_id', $communeId)
            ->orderBy('libelle')
            ->get();
    }
}
