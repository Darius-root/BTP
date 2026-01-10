<?php

namespace App\Http\Controllers;

use App\Models\NiveauBatiment;
use App\Models\Batiment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;

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

        $niveaux = NiveauBatiment::with(['user'])
            ->latest()
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

        $batiments = Batiment::all();

        return Inertia::render('Organisations/NiveauxBatiment/Create', [
            'batiments' => $batiments,
        ]);
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

        $validated['user_id'] = Auth::id();

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
            'niveau' => $niveauBatiment->load('user'),
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

        $batiments = Batiment::all();

        return Inertia::render('Organisations/NiveauxBatiment/Edit', [
            'niveau' => $niveauBatiment,
            'batiments' => $batiments,
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
            ->route('niveaux.index')
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
