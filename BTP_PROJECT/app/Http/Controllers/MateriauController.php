<?php

namespace App\Http\Controllers;

use App\Models\Materiau;
use App\Models\UniteMesure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MateriauController extends Controller
{
    /**
     * Afficher la liste des matériaux
     */
    public function index()
    {
        if (!Auth::user()->can('SYSTEM_MATERIAU_VIEW')) {
            return back()->with(
                'error',
                "Vous n’êtes pas autorisé à consulter la liste des matériaux."
            );
        }

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
        if (!Auth::user()->can('SYSTEM_MATERIAU_CREATE')) {
            return back()->with(
                'error',
                "Vous n’avez pas l’autorisation de créer un matériel."
            );
        }

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
        if (!Auth::user()->can('SYSTEM_MATERIAU_CREATE')) {
            return back()->with(
                'error',
                "Vous n’avez pas l’autorisation de créer un matériel."
            );
        }

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:materiaux',
            'nom' => 'required|string|max:255|unique:materiaux',
            'unite_id' => 'required|exists:unites_mesure,id',
        ]);

        Materiau::create($validated);

        return redirect()
            ->route('materiaux.index')
            ->with('success', "Le matériel a été créé avec succès.");
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        if (!Auth::user()->can('SYSTEM_MATERIAU_EDIT')) {
            return back()->with(
                'error',
                "Vous n’avez pas l’autorisation de modifier ce matériel."
            );
        }

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
        if (!Auth::user()->can('SYSTEM_MATERIAU_EDIT')) {
            return back()->with(
                'error',
                "Vous n’avez pas l’autorisation de modifier ce matériel."
            );
        }

        $materiau = Materiau::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:materiaux,code,' . $materiau->id,
            'nom' => 'required|string|max:255|unique:materiaux,nom,' . $materiau->id,
            'unite_id' => 'required|exists:unites_mesure,id',
        ]);

        $materiau->update($validated);

        return redirect()
            ->route('materiaux.index')
            ->with('success', "Le matériel été mis à jour avec succès.");
    }

    /**
     * Supprimer un matériau
     */
    public function destroy($id)
    {
        if (!Auth::user()->can('SYSTEM_MATERIAU_DELETE')) {
            return back()->with(
                'error',
                "Vous n’avez pas l’autorisation de supprimer ce matériel."
            );
        }

        $materiau = Materiau::findOrFail($id);

        if ($materiau->collectionsPrix()->exists()) {
            return back()->with(
                'error',
                "Ce matériel ne peut pas être supprimé car il est utilisé dans des collections de prix."
            );
        }

        $materiau->delete();

        return redirect()
            ->route('materiaux.index')
            ->with('success', "Le matériel a été supprimé avec succès.");
    }
}
