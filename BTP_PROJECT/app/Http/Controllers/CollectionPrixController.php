<?php

namespace App\Http\Controllers;

use App\Models\CollectionPrix;
use App\Models\Commune;
use App\Models\Arrondissement;
use App\Models\Materiau;
use App\Models\Devise;
use App\Models\CorpsEtat;
use App\Models\UniteMesure;
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

            // Contrôle des permissions
            $permissions = [
                'canView' => $user->can('SYSTEM_COLLECTION_VIEW'),
                'canCreate' => $user->can('SYSTEM_COLLECTION_CREATE'),
                'canEdit' => $user->can('SYSTEM_COLLECTION_EDIT'),
                'canDelete' => $user->can('SYSTEM_COLLECTION_DELETE'),
                'canValidate' => $user->can('SYSTEM_COLLECTION_VALIDATE'),
            ];

            if (!$permissions['canView']) {
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

            // Les non-admins ne voient que leurs propres collectes
            $isSystemAdmin = $user->hasRole('SYSTEM_ADMIN_PLATEFORME');
            if (!$isSystemAdmin) {
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

            // Ajouter les permissions spécifiques à chaque collecte
            $collectionsData = $collections->items();
            foreach ($collectionsData as $collection) {
                $collection->can_edit = $permissions['canEdit']
                    && !$collection->is_validated
                    && $collection->user_id === $user->id;

                $collection->can_delete = $permissions['canDelete']
                    && !$collection->is_validated;

                $collection->can_validate = $permissions['canValidate']
                    && !$collection->is_validated;
            }

            return Inertia::render('CollectionsPrix/Index', [
                'collections' => [
                    'data' => $collectionsData,
                    'links' => $collections->links()->elements[0] ?? [],
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
                'permissions' => $permissions,
                'showCollectorColumn' => $isSystemAdmin,
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

            // Contrôle de permission
            if (!$user->can('SYSTEM_COLLECTION_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer une collecte.");
            }

            return Inertia::render('CollectionsPrix/Create', [
                'communes' => Commune::orderBy('libelle')->get(),
                'materiaux' => Materiau::with('unite')->orderBy('nom')->get(),
                'unites' => UniteMesure::orderBy('libelle')->get(),
                'devises' => Devise::orderBy('libelle')->get(),
                'categories' => CorpsEtat::orderBy('intitule')->get(),
            ]);

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Détails d'une collecte
     */
    public function show($id)
    {
        try {
            $user = Auth::user();

            // Contrôle de permission VIEW
            if (!$user->can('SYSTEM_COLLECTION_VIEW')) {
                return back()->with('error', "Vous n'avez pas la permission de voir les collections de prix.");
            }

            $collection = CollectionPrix::with([
                'commune',
                'arrondissement',
                'materiau.unite',
                'devise',
                'categorie',
                'user',
                'validator',
                'unite',
            ])->findOrFail($id);

            // Vérifier que l'utilisateur est admin OU propriétaire
            $isSystemAdmin = $user->hasRole('SYSTEM_ADMIN_PLATEFORME');
            if (!$isSystemAdmin && $collection->user_id !== $user->id) {
                return back()->with('error', "Vous n'avez pas la permission de voir cette collecte.");
            }

            // Calcul des permissions pour cette collecte spécifique
            $permissions = [
                'canEdit' => $user->can('SYSTEM_COLLECTION_EDIT')
                    && !$collection->is_validated
                    && $collection->user_id === $user->id,
                'canDelete' => $user->can('SYSTEM_COLLECTION_DELETE')
                    && !$collection->is_validated,
                'canValidate' => $user->can('SYSTEM_COLLECTION_VALIDATE')
                    && !$collection->is_validated,
            ];

            return Inertia::render('CollectionsPrix/Show', [
                'collection' => $collection,
                'permissions' => $permissions,
            ]);

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Enregistrement d'une collecte (toujours NON validée)
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            // Contrôle de permission
            if (!$user->can('SYSTEM_COLLECTION_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer une collecte.");
            }

            $validated = $request->validate([
                'commune_id' => 'required|exists:communes,id',
                'arrondissement_id' => 'nullable|exists:arrondissements,id',
                'quartier' => 'nullable|string|max:255',
                'materiau_id' => 'required|exists:materiaux,id',
                'devise_id' => 'required|exists:devises,id',
                'categorie_id' => 'required|exists:corps_etat,id',
                'unite_id' => 'required|exists:unites_mesure,id',
                'description_materiaux' => 'required|string',
                'detail' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'point_vente' => 'nullable|string|max:255',
            ]);

            CollectionPrix::create([
                ...$validated,
                'user_id' => $user->id,
                'is_validated' => false,
            ]);

            return redirect()->route('collections-prix.index')
                ->with('success', "Collecte enregistrée et envoyée pour validation.");

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        try {
            $user = Auth::user();

            // Contrôle de permission EDIT
            if (!$user->can('SYSTEM_COLLECTION_EDIT')) {
                return back()->with('error', "Vous n'avez pas la permission de modifier les collectes.");
            }

            $collection = CollectionPrix::findOrFail($id);

            // Vérifier propriétaire + non validée
            if ($collection->user_id !== $user->id) {
                return back()->with('error', "Vous ne pouvez modifier que vos propres collectes.");
            }

            if ($collection->is_validated) {
                return back()->with('error', "Une collecte validée ne peut pas être modifiée.");
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
                'unites' => UniteMesure::orderBy('libelle')->get(),
                'devises' => Devise::orderBy('libelle')->get(),
                'categories' => CorpsEtat::orderBy('intitule')->get(),
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

            // Contrôle de permission EDIT
            if (!$user->can('SYSTEM_COLLECTION_EDIT')) {
                return back()->with('error', "Vous n'avez pas la permission de modifier les collectes.");
            }

            $collection = CollectionPrix::findOrFail($id);

            // Vérifier propriétaire + non validée
            if ($collection->user_id !== $user->id) {
                return back()->with('error', "Vous ne pouvez modifier que vos propres collectes.");
            }

            if ($collection->is_validated) {
                return back()->with('error', "Une collecte validée ne peut pas être modifiée.");
            }

            $validated = $request->validate([
                'commune_id' => 'required|exists:communes,id',
                'arrondissement_id' => 'nullable|exists:arrondissements,id',
                'quartier' => 'nullable|string|max:255',
                'materiau_id' => 'required|exists:materiaux,id',
                'devise_id' => 'required|exists:devises,id',
                'categorie_id' => 'required|exists:corps_etat,id',
                'unite_id' => 'required|exists:unites_mesure,id',
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
     * Validation d'une collecte
     */
    public function validateCollection($id)
    {
        try {
            $user = Auth::user();

            // Contrôle de permission VALIDATE
            if (!$user->can('SYSTEM_COLLECTION_VALIDATE')) {
                return back()->with('error', "Vous n'avez pas la permission de valider les collectes.");
            }

            $collection = CollectionPrix::findOrFail($id);

            // Vérifier si déjà validée
            if ($collection->is_validated) {
                return back()->with('error', "Cette collecte est déjà validée.");
            }

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
     * Suppression d'une collecte
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();

            // Contrôle de permission DELETE
            if (!$user->can('SYSTEM_COLLECTION_DELETE')) {
                return back()->with('error', "Vous n'avez pas la permission de supprimer les collectes.");
            }

            $collection = CollectionPrix::findOrFail($id);

          
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
