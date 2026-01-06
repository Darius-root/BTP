<?php

namespace App\Http\Controllers;

use App\Models\CorpsEtat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CorpsEtatController extends Controller
{
    /**
     * Afficher la liste des corps d'état
     */
    public function index()
    {
        $corpsEtats = CorpsEtat::where('user_id', Auth::id())
            ->orderBy('ordre')
            ->get();

        return Inertia::render('CorpsEtat/Index', [
            'corpsEtats' => $corpsEtats,
        ]);
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return Inertia::render('CorpsEtat/Create');
    }

    /**
     * Enregistrer un nouveau corps d'état
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:corps_etats',
            'intitule' => 'required|string|max:255|unique:corps_etats',
            'ordre' => 'required|integer',
            'sous_total' => 'nullable|numeric|min:0',
        ]);

        CorpsEtat::create(array_merge($validated, [
            'user_id' => Auth::id(),
        ]));

        return redirect()->route('corps-etat.index')
            ->with('success', 'Corps d\'état créé avec succès.');
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(CorpsEtat $corpsEtat)
    {
        return Inertia::render('CorpsEtat/Edit', [
            'corpsEtat' => $corpsEtat,
        ]);
    }

    /**
     * Mettre à jour un corps d'état
     */
    public function update(Request $request, CorpsEtat $corpsEtat)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:corps_etats,code,' . $corpsEtat->id,
            'intitule' => 'required|string|max:255|unique:corps_etats,intitule,' . $corpsEtat->id,
            'ordre' => 'required|integer',
            'sous_total' => 'nullable|numeric|min:0',
        ]);

        $corpsEtat->update($validated);

        return redirect()->route('corps-etat.index')
            ->with('success', 'Corps d\'état mis à jour avec succès.');
    }

    /**
     * Supprimer un corps d'état
     */
    public function destroy(CorpsEtat $corpsEtat)
    {
        $corpsEtat->delete();

        return redirect()->route('corps-etat.index')
            ->with('success', 'Corps d\'état supprimé avec succès.');
    }
}
