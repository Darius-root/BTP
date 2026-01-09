<?php

namespace App\Http\Controllers;

use App\Models\CollectionPrix;
use App\Models\Commune;
use App\Models\Arrondissement;
use App\Models\Materiau;
use App\Models\Devise;
use App\Models\CorpsEtat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CollectionPrixController extends Controller
{
    /**
     * Afficher la liste des collections de prix
     */
    public function index(Request $request)
    {


        $query = CollectionPrix::with([
            'commune',
            'arrondissement',
            'materiau.unite',
            'devise',
            'categorie',
            'user'
        ]);

        // Filtres
        if ($request->filled('commune_id')) {
            $query->where('commune_id', $request->commune_id);
        }

        if ($request->filled('categorie_id')) {
            $query->where('categorie_id', $request->categorie_id);
        }

        $collections = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        $communes = Commune::orderBy('libelle')->get();
        $categories = CorpsEtat::where('user_id', Auth::id())->orderBy('intitule')->get();

        return Inertia::render('CollectionsPrix/Index', [
            'collections' => $collections,
            'communes' => $communes,
            'categories' => $categories,
            'filters' => $request->only(['commune_id', 'categorie_id']),
        ]);
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $communes = Commune::orderBy('libelle')->get();
        $materiaux = Materiau::with('unite')->orderBy('nom')->get();
        $devises = Devise::orderBy('libelle')->get();
        $categories = CorpsEtat::where('user_id', Auth::id())->orderBy('intitule')->get();

        return Inertia::render('CollectionsPrix/Create', [
            'communes' => $communes,
            'materiaux' => $materiaux,
            'devises' => $devises,
            'categories' => $categories,
        ]);
    }

    /**
     * Récupérer les arrondissements d'une commune
     */
    public function getArrondissements($communeId)
    {
        $arrondissements = Arrondissement::where('commune_id', $communeId)
            ->orderBy('libelle')
            ->get();

        return response()->json($arrondissements);
    }

    /**
     * Enregistrer une nouvelle collection de prix
     */
    public function store(Request $request)
    {
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

        CollectionPrix::create(array_merge($validated, [
            'user_id' => Auth::id(),
            'status' => true,
        ]));

        return redirect()->route('collections-prix.index')
            ->with('success', 'Prix ajouté à la collection avec succès.');
    }

    /**
     * Afficher le formulaire d'édition
     */
    /**
     * Afficher le formulaire d'édition
     */
    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        // Utilisez findOrFail pour gérer le cas où l'ID n'existe pas
        $collection = CollectionPrix::with([
            'commune',
            'arrondissement',
            'materiau.unite',
            'devise',
            'categorie',
            'user'
        ])->findOrFail($id);

        $communes = Commune::orderBy('libelle')->get();
        $materiaux = Materiau::with('unite')->orderBy('nom')->get();
        $devises = Devise::orderBy('libelle')->get();
        $categories = CorpsEtat::where('user_id', Auth::id())->orderBy('intitule')->get();

        return Inertia::render('CollectionsPrix/Edit', [
            'collection' => $collection,
            'communes' => $communes,
            'materiaux' => $materiaux,
            'devises' => $devises,
            'categories' => $categories,
        ]);
    }

    /**
     * Mettre à jour une collection de prix
     */
    /**
     * Mettre à jour une collection de prix
     */
    public function update(Request $request, $id)
    {
        // Trouver la collection
        $collectionPrix = CollectionPrix::findOrFail($id);

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
            'status' => 'required|boolean',
        ]);

        $collectionPrix->update($validated);

        return redirect()->route('collections-prix.index')
            ->with('success', 'Prix mis à jour avec succès.');
    }

    /**
     * Supprimer une collection de prix
     */
    public function destroy($id)
    {
        $collectionPrix = CollectionPrix::findOrFail($id);
        $collectionPrix->delete();

        return redirect()->route('collections-prix.index')
            ->with('success', 'Prix supprimé avec succès.');
    }
}
