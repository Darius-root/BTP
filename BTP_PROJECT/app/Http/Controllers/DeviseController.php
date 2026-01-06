<?php

namespace App\Http\Controllers;

use App\Models\Devise;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeviseController extends Controller
{
    /**
     * Afficher la liste des devises
     */
    public function index()
    {
        $devises = Devise::orderBy('code')->get();

        return Inertia::render('Devises/Index', [
            'devises' => $devises,
        ]);
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return Inertia::render('Devises/Create');
    }

    /**
     * Enregistrer une nouvelle devise
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:devises',
            'libelle' => 'required|string|max:255|unique:devises',
            'symbole' => 'nullable|string|max:10|',
        ]);

        Devise::create($validated);

        return redirect()->route('devises.index')
            ->with('success', 'Devise créée avec succès.');
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Devise $devise)
    {
        return Inertia::render('Devises/Edit', [
            'devise' => $devise,
        ]);
    }

    /**
     * Mettre à jour une devise
     */
    public function update(Request $request, Devise $devise)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:devises,code,' . $devise->id,
            'libelle' => 'required|string|max:255|unique:devises,libelle,' . $devise->id,
            'symbole' => 'nullable|string|max:10',
        ]);

        $devise->update($validated);

        return redirect()->route('devises.index')
            ->with('success', 'Devise mise à jour avec succès.');
    }

    /**
     * Supprimer une devise
     */
    public function destroy(Devise $devise)
    {
        $devise->delete();

        return redirect()->route('devises.index')
            ->with('success', 'Devise supprimée avec succès.');
    }
}
