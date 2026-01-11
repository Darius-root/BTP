<?php

namespace App\Http\Controllers;

use App\Models\NiveauBatiment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NiveauBatimentController extends Controller
{
    /**
     * Liste globale des niveaux
     */
    public function index()
    {
        if (!Auth::user()->can('SYSTEM_NIVEAU_BATIMENT_VIEW')) {
            abort(403, "Vous n'avez pas la permission de consulter les niveaux.");
        }

        $niveaux = NiveauBatiment::latest()
            ->paginate(10);

        return Inertia::render('Organisations/NiveauxBatiment/Index', [
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

        return Inertia::render('Organisations/NiveauxBatiment/Create');
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
            abort(403, "Vous n'avez pas la permission de consulter ce niveau.");
        }

        return Inertia::render('Organisations/NiveauxBatiment/Show', [
            'niveau' => $niveauBatiment,
        ]);
    }

    /**
     * Formulaire d'édition
     */
    public function edit(NiveauBatiment $niveauBatiment)
    {
        if (!Auth::user()->can('SYSTEM_NIVEAU_BATIMENT_EDIT')) {
            abort(403, "Vous n'avez pas la permission de modifier ce niveau.");
        }

        return Inertia::render('Organisations/NiveauxBatiment/Edit', [
            'niveau' => $niveauBatiment,
        ]);
    }

    /**
     * Mise à jour d'un niveau
     */
    public function update(Request $request, NiveauBatiment $niveauBatiment)
    {
        if (!Auth::user()->can('SYSTEM_NIVEAU_BATIMENT_EDIT')) {
            abort(403, "Vous n'avez pas la permission de modifier ce niveau.");
        }

        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:niveaux_batiment,code,' . $niveauBatiment->id,
            'nom' => 'required|string|max:255|unique:niveaux_batiment,nom,' . $niveauBatiment->id,
            'description' => 'nullable|string',
        ]);

        $niveauBatiment->update($validated);

        return redirect()
            ->route('niveaux-batiment.index')
            ->with('success', 'Niveau mis à jour avec succès.');
    }

    /**
     * Suppression d'un niveau
     */
    public function destroy(NiveauBatiment $niveauBatiment)
    {
        if (!Auth::user()->can('SYSTEM_NIVEAU_BATIMENT_DELETE')) {
            abort(403, "Vous n'avez pas la permission de supprimer ce niveau.");
        }

        $niveauBatiment->delete();

        return redirect()
            ->route('niveaux-batiment.index')
            ->with('success', 'Niveau supprimé avec succès.');
    }
}
