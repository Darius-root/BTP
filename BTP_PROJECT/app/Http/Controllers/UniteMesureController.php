<?php

namespace App\Http\Controllers;

use App\Models\UniteMesure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UniteMesureController extends Controller
{
    /**
     * Afficher la liste des unités de mesure
     */
    public function index()
    {
        $unites = UniteMesure::orderBy('code')->get();

        return Inertia::render('UnitesMesure/Index', [
            'unites' => $unites,
        ]);
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return Inertia::render('UnitesMesure/Create');
    }

    /**
     * Enregistrer une nouvelle unité de mesure
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:unites_mesure',
            'libelle' => 'required|string|max:255|unique:unites_mesure',
        ]);

        UniteMesure::create($validated);

        return redirect()->route('unites-mesure.index')
            ->with('success', 'Unité de mesure créée avec succès.');
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(UniteMesure $uniteMesure)
    {
        return Inertia::render('UnitesMesure/Edit', [
            'unite' => $uniteMesure,
        ]);
    }

    /**
     * Mettre à jour une unité de mesure
     */
    public function update(Request $request, UniteMesure $uniteMesure)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:unites_mesure,code,' . $uniteMesure->id,
            'libelle' => 'required|string|max:255|unique:unites_mesure,libelle,' . $uniteMesure->id,
        ]);

        $uniteMesure->update($validated);

        return redirect()->route('unites-mesure.index')
            ->with('success', 'Unité de mesure mise à jour avec succès.');
    }

    /**
     * Supprimer une unité de mesure
     */
    public function destroy(UniteMesure $uniteMesure)
    {
        $uniteMesure->delete();

        return redirect()->route('unites-mesure.index')
            ->with('success', 'Unité de mesure supprimée avec succès.');
    }
}
