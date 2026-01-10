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
        if (!Auth::user()->can('SYSTEM_COLLECTION_VIEW')) {
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de consulter les collections de prix.');
        }

        $query = CollectionPrix::with([
            'commune',
            'arrondissement',
            'materiau.unite',
            'devise',
            'categorie',
            'user'
        ]);

        if ($request->filled('commune_id')) {
            $query->where('commune_id', $request->commune_id);
        }

        if ($request->filled('categorie_id')) {
            $query->where('categorie_id', $request->categorie_id);
        }

        $collections = $query->orderBy('created_at', 'desc')->paginate(15);

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
        if (!Auth::user()->can('SYSTEM_COLLECTION_CREATE')) {
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de créer une collection de prix.');
        }

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
        if (!Auth::user()->can('SYSTEM_COLLECTION_CREATE')) {
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de créer une collection de prix.');
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
        ], [
            'commune_id.required' => 'La commune est obligatoire.',
            'materiau_id.required' => 'Le matériau est obligatoire.',
            'devise_id.required' => 'La devise est obligatoire.',
            'categorie_id.required' => 'La catégorie est obligatoire.',
            'description_materiaux.required' => 'La description du matériau est obligatoire.',
            'price.required' => 'Le prix est obligatoire.',
            'price.numeric' => 'Le prix doit être un nombre.',
            'price.min' => 'Le prix ne peut pas être négatif.',
        ]);

        try {
            CollectionPrix::create(array_merge($validated, [
                'user_id' => Auth::id(),
                'status' => true,
            ]));

            return redirect()->route('collections-prix.index')
                ->with('success', 'Prix ajouté à la collection avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de l’ajout du prix. Veuillez réessayer.')
                ->withInput();
        }
    }

    /**
     * Afficher le formulaire d’édition
     */
    public function edit($id)
    {
        if (!Auth::user()->can('SYSTEM_COLLECTION_EDIT')) {
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de modifier cette collection de prix.');
        }

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
    public function update(Request $request, $id)
    {
        if (!Auth::user()->can('SYSTEM_COLLECTION_EDIT')) {
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de modifier cette collection de prix.');
        }

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

        try {
            $collectionPrix->update($validated);

            return redirect()->route('collections-prix.index')
                ->with('success', 'Prix mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la mise à jour du prix. Veuillez réessayer.')
                ->withInput();
        }
    }

    /**
     * Supprimer une collection de prix
     */
    public function destroy($id)
    {
        if (!Auth::user()->can('SYSTEM_COLLECTION_DELETE')) {
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de supprimer cette collection de prix.');
        }

        $collectionPrix = CollectionPrix::findOrFail($id);

        try {
            $collectionPrix->delete();

            return redirect()->route('collections-prix.index')
                ->with('success', 'Prix supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la suppression du prix. Veuillez réessayer.');
        }
    }
}
