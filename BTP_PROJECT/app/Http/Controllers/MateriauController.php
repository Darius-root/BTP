<?php

namespace App\Http\Controllers;

use App\Models\Materiau;
use App\Models\UniteMesure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MateriauController extends Controller
{
    /**
     * Afficher la liste des matériaux
     */
    public function index()
    {
        $materiaux = Materiau::with('unite')
            ->orderBy('code')
            ->get();

        return Inertia::render('Materiaux/Index', [
            'materiaux' => $materiaux,
        ]);
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $unites = UniteMesure::orderBy('libelle')->get();

        return Inertia::render('Materiaux/Create', [
            'unites' => $unites,
        ]);
    }

    /**
     * Enregistrer un nouveau matériau
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:materiaux',
            'nom' => 'required|string|max:255|unique:materiaux',
            'unite_id' => 'required|exists:unites_mesure,id',
        ]);

        Materiau::create($validated);

        return redirect()->route('materiaux.index')
            ->with('success', 'Matériau créé avec succès.');
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        $materiau = Materiau::with('unite')->findOrFail($id);
        $unites = UniteMesure::orderBy('libelle')->get();

        return Inertia::render('Materiaux/Edit', [
            'materiau' => $materiau,
            'unites' => $unites,
        ]);
    }

    /**
     * Mettre à jour un matériau
     */
    public function update(Request $request, $id)
    {
        $materiau = Materiau::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:materiaux,code,' . $materiau->id,
            'nom' => 'required|string|max:255|unique:materiaux,nom,' . $materiau->id,
            'unite_id' => 'required|exists:unites_mesure,id',
        ]);

        $materiau->update($validated);

        return redirect()->route('materiaux.index')
            ->with('success', 'Matériau mis à jour avec succès.');
    }

    /**
     * Supprimer un matériau
     */
    public function destroy($id)
    {
        $materiau = Materiau::findOrFail($id);

        // Vérifier si le matériau est utilisé dans des collections de prix
        if ($materiau->collectionsPrix()->exists()) {
            return redirect()->route('materiaux.index')
                ->with('error', 'Ce matériau ne peut pas être supprimé car il est utilisé dans des collections de prix.');
        }

        $materiau->delete();

        return redirect()->route('materiaux.index')
            ->with('success', 'Matériau supprimé avec succès.');
    }
}
