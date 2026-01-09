<?php

namespace App\Http\Controllers;

use App\Models\Arrondissement;
use App\Models\Commune;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ArrondissementController extends Controller
{
    /**
     * Afficher la liste des arrondissements
     */
    public function index()
    {
        if (!Auth::user()->can('SYSTEM_ARRONDISSEMENT_VIEW')) {
            return redirect()->back()->with('error', 'Permission refusée.');
        }

        $arrondissements = Arrondissement::with('commune')
            ->orderBy('code')
            ->get();

        return Inertia::render('Arrondissements/Index', [
            'arrondissements' => $arrondissements,
        ]);
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        if (!Auth::user()->can('SYSTEM_ARRONDISSEMENT_CREATE')) {
            return redirect()->back()->with('error', 'Permission refusée.');
        }

        $communes = Commune::orderBy('libelle')->get();

        return Inertia::render('Arrondissements/Create', [
            'communes' => $communes,
        ]);
    }

    /**
     * Enregistrer un nouvel arrondissement
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('SYSTEM_ARRONDISSEMENT_CREATE')) {
            return redirect()->back()->with('error', 'Permission refusée.');
        }

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:arrondissements',
            'libelle' => 'required|string|max:255|unique:arrondissements',
            'commune_id' => 'required|exists:communes,id',
        ]);

        Arrondissement::create($validated);

        return redirect()
            ->route('arrondissements.index')
            ->with('success', 'Arrondissement créé avec succès.');
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Arrondissement $arrondissement)
    {
        if (!Auth::user()->can('SYSTEM_ARRONDISSEMENT_EDIT')) {
            return redirect()->back()->with('error', 'Permission refusée.');
        }

        $communes = Commune::orderBy('libelle')->get();

        return Inertia::render('Arrondissements/Edit', [
            'arrondissement' => $arrondissement,
            'communes' => $communes,
        ]);
    }

    /**
     * Mettre à jour un arrondissement
     */
    public function update(Request $request, Arrondissement $arrondissement)
    {
        if (!Auth::user()->can('SYSTEM_ARRONDISSEMENT_EDIT')) {
            return redirect()->back()->with('error', 'Permission refusée.');
        }

        $validated = $request->validate([
            'code' => 'required|string|max:10',
            'libelle' => 'required|string|max:255',
            'commune_id' => 'required|exists:communes,id',
        ]);

        $arrondissement->update($validated);

        return redirect()
            ->route('arrondissements.index')
            ->with('success', 'Arrondissement mis à jour avec succès.');
    }

    /**
     * Supprimer un arrondissement
     */
    public function destroy(Arrondissement $arrondissement)
    {
        if (!Auth::user()->can('SYSTEM_ARRONDISSEMENT_DELETE')) {
            return redirect()->back()->with('error', 'Permission refusée.');
        }

        $arrondissement->delete();

        return redirect()
            ->route('arrondissements.index')
            ->with('success', 'Arrondissement supprimé avec succès.');
    }
}
