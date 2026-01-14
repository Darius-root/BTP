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

        $niveaux = NiveauBatiment::with('user')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return Inertia::render('NiveauxBatiment/Index', [
            'niveaux' => $niveaux,
        ]);
    }

    public function show(NiveauBatiment $niveauBatiment)
    {
        if (!Auth::user()->can('SYSTEM_NIVEAU_BATIMENT_VIEW')) {
            return redirect()->back()->with('error', "Vous n'avez pas la permission de consulter ce niveau de bâtiment.");
        }

        return Inertia::render('NiveauxBatiment/Show', [
            'niveau' => $niveauBatiment->load('user'),
        ]);
    }


    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        if (!Auth::user()->can('SYSTEM_NIVEAU_BATIMENT_CREATE')) {
            return redirect()->back()->with('error', "Vous n'avez pas la permission de créer un niveau de bâtiment.");
        }

        return Inertia::render('NiveauxBatiment/Create');
    }

    /**
     * Enregistrer un nouveau niveau
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('SYSTEM_NIVEAU_BATIMENT_CREATE')) {
            return redirect()->back()->with('error', "Vous n'avez pas la permission de créer un niveau de bâtiment.");
        }

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:niveaux_batiment',
            'nom' => 'required|string|max:255|unique:niveaux_batiment',
            'description' => 'nullable|string',
        ], [
            'code.required' => 'Le code est obligatoire.',
            'code.unique' => 'Ce code existe déjà.',
            'nom.required' => 'Le nom est obligatoire.',
            'nom.unique' => 'Ce nom existe déjà.',
        ]);

        try {
            NiveauBatiment::create(array_merge($validated, [
                'user_id' => Auth::id(),
            ]));

            return redirect()
                ->route('niveaux-batiment.index')
                ->with('success', 'Le niveau de bâtiment a été créé avec succès.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Une erreur est survenue lors de la création du niveau de bâtiment. Veuillez réessayer.')
                ->withInput();
        }
    }

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
