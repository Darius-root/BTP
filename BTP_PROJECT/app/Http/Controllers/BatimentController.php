<?php

namespace App\Http\Controllers;

use App\Models\Batiment;
use App\Models\Projet;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class BatimentController extends Controller
{
    /**
     * Liste globale des bâtiments
     */
    public function index(): Response
    {
        $batiments = Batiment::with('projet')
            ->latest()
            ->paginate(10);

        return Inertia::render('Organisations/Projets/Batiments/Index', [
            'batiments' => $batiments,
        ]);
    }

    /**
     * Liste des bâtiments d'un projet précis
     */
    public function indexByProjet(Projet $projet): Response
    {
        $batiments = Batiment::where('projet_id', $projet->id)
            ->with('projet')
            ->latest()
            ->paginate(10);

        return Inertia::render('Organisations/Projets/Batiments/Index', [
            'batiments' => $batiments,
            'projet' => $projet, // utilisé pour le titre et les boutons
        ]);
    }

    /**
     * Création globale (avec choix du projet)
     */
    public function create(): Response
    {
        dd(Projet::all());
        return Inertia::render('Organisations/Projets/Batiments/Create', [
            'projets' => Projet::all(),
        ]);
    }

    /**
     * Création depuis un projet précis
     */
    public function createFromProjet(Projet $projet): Response
    {
        // dd($projet);
        return Inertia::render('Organisations/Projets/Batiments/Create', [
            'projet' => $projet,
        ]);
    }

    /**
     * Enregistrement
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:batiments,code',
            'nom' => 'required|string|max:255',
            'localisation' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'projet_id' => 'required|exists:projets,id',
        ]);

        $batiment = Batiment::create($validated);
// dd($batiment);
        // 🔁 Retour intelligent vers le projet
        return redirect()
            ->route('projets.batiments.index', $batiment->projet_id)
            ->with('success', 'Bâtiment créé avec succès.');
    }

    public function show(Batiment $batiment): Response
    {
        $batiment->load('projet');

        return Inertia::render('Organisations/Projets/Batiments/Show', [
            'batiment' => $batiment,
        ]);
    }

    public function edit(Batiment $batiment): Response
    {
        return Inertia::render('Organisations/Projets/Batiments/Edit', [
            'batiment' => $batiment,
            'projets' => Projet::all(),
        ]);
    }

    public function update(Request $request, Batiment $batiment): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:batiments,code,' . $batiment->id,
            'nom' => 'required|string|max:255',
            'localisation' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'projet_id' => 'required|exists:projets,id',
        ]);

        $batiment->update($validated);

        return redirect()
            ->route('projets.batiments.index', $batiment->projet_id)
            ->with('success', 'Bâtiment mis à jour avec succès.');
    }

    public function destroy(Batiment $batiment): RedirectResponse
    {
        $projetId = $batiment->projet_id;

        $batiment->delete();

        return redirect()
            ->route('projets.batiments.index', $projetId)
            ->with('success', 'Bâtiment supprimé avec succès.');
    }
}
