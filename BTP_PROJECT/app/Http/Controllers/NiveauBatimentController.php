<?php

namespace App\Http\Controllers;

use App\Models\NiveauBatiment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NiveauBatimentController extends Controller
{
    /**
     * Afficher la liste des niveaux de bâtiment
     */
    public function index()
    {
        if (!Auth::user()->can('SYSTEM_NIVEAU_BATIMENT_VIEW')) {
            return redirect()->back()->with('error', "Vous n'avez pas la permission de consulter les niveaux de bâtiment.");
        }

        $niveaux = NiveauBatiment::latest()
            ->paginate(10);

        return Inertia::render('NiveauxBatiment/Index', [
            'niveaux' => $niveaux,
        ]);
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        if (!Auth::user()->can('SYSTEM_NIVEAU_BATIMENT_CREATE')) {
            abort(403, "Vous n'avez pas la permission de créer un niveau.");
        }

        return Inertia::render('NiveauxBatiment/Create');
    }

    /**
     * Enregistrement d'un niveau
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('SYSTEM_NIVEAU_BATIMENT_CREATE')) {
            abort(403, "Vous n'avez pas la permission de créer un niveau.");
        }

        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:niveaux_batiment,code',
            'nom' => 'required|string|max:255|unique:niveaux_batiment,nom',
            'description' => 'nullable|string',
        ]);


        NiveauBatiment::create($validated);

        return redirect()
            ->route('niveaux-batiment.index')
            ->with('success', 'Niveau créé avec succès.');
    }

    /**
     * Détail d'un niveau
     */
    public function show(NiveauBatiment $niveauBatiment)
    {
        if (!Auth::user()->can('SYSTEM_NIVEAU_BATIMENT_VIEW')) {
            return redirect()->back()->with('error', "Vous n'avez pas la permission de consulter ce niveau de bâtiment.");
        }

        return Inertia::render('NiveauxBatiment/Show', [
            'niveau' => $niveauBatiment,
        ]);
    }


    /**
     * Afficher le formulaire de création
     */
   

    /**
     * Enregistrer un nouveau niveau
     */
    

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(NiveauBatiment $niveauBatiment)
    {
        if (!Auth::user()->can('SYSTEM_NIVEAU_BATIMENT_EDIT')) {
            return redirect()->back()->with('error', "Vous n'avez pas la permission de modifier ce niveau de bâtiment.");
        }

        return Inertia::render('NiveauxBatiment/Edit', [
            'niveau' => $niveauBatiment,
        ]);
    }

    /**
     * Mettre à jour un niveau
     */
    public function update(Request $request, NiveauBatiment $niveauBatiment)
    {
        if (!Auth::user()->can('SYSTEM_NIVEAU_BATIMENT_EDIT')) {
            return redirect()->back()->with('error', "Vous n'avez pas la permission de modifier ce niveau de bâtiment.");
        }

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:niveaux_batiment,code,' . $niveauBatiment->id,
            'nom' => 'required|string|max:255|unique:niveaux_batiment,nom,' . $niveauBatiment->id,
            'description' => 'nullable|string',
        ], [
            'code.required' => 'Le code est obligatoire.',
            'code.unique' => 'Ce code existe déjà.',
            'nom.required' => 'Le nom est obligatoire.',
            'nom.unique' => 'Ce nom existe déjà.',
        ]);

        try {
            $niveauBatiment->update($validated);

            return redirect()
                ->route('niveaux-batiment.index')
                ->with('success', 'Le niveau de bâtiment a été mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Une erreur est survenue lors de la mise à jour du niveau de bâtiment. Veuillez réessayer.')
                ->withInput();
        }
    }

    /**
     * Supprimer un niveau
     */
    public function destroy(NiveauBatiment $niveauBatiment)
    {
        if (!Auth::user()->can('SYSTEM_NIVEAU_BATIMENT_DELETE')) {
            return redirect()->back()->with('error', "Vous n'avez pas la permission de supprimer ce niveau de bâtiment.");
        }

        try {
            $niveauBatiment->delete();

            return redirect()
                ->route('niveaux-batiment.index')
                ->with('success', 'Le niveau de bâtiment a été supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Une erreur est survenue lors de la suppression du niveau de bâtiment. Veuillez réessayer.');
        }
    }
}
