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
        if (!Auth::user()->can('SYSTEM_CORPS_ETAT_VIEW')) {
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de consulter les corps d’état.');
        }

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
        if (!Auth::user()->can('SYSTEM_CORPS_ETAT_CREATE')) {
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de créer un corps d’état.');
        }

        return Inertia::render('CorpsEtat/Create');
    }

    /**
     * Enregistrer un nouveau corps d'état
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('SYSTEM_CORPS_ETAT_CREATE')) {
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de créer un corps d’état.');
        }

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:corps_etat',
            'intitule' => 'required|string|max:255|unique:corps_etat',
            'ordre' => 'required|integer',
            'sous_total' => 'nullable|numeric|min:0',
        ], [
            'code.required' => 'Le code est obligatoire.',
            'code.unique' => 'Ce code existe déjà.',
            'intitule.required' => 'L’intitulé est obligatoire.',
            'intitule.unique' => 'Cet intitulé existe déjà.',
            'ordre.required' => 'L’ordre est obligatoire.',
            'ordre.integer' => 'L’ordre doit être un nombre entier.',
            'sous_total.numeric' => 'Le sous-total doit être un nombre.',
            'sous_total.min' => 'Le sous-total ne peut pas être négatif.',
        ]);

        try {
            CorpsEtat::create(array_merge($validated, [
                'user_id' => Auth::id(),
            ]));

            return redirect()
                ->route('corps-etat.index')
                ->with('success', 'Le corps d’état a été créé avec succès.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Une erreur est survenue lors de la création du corps d’état. Veuillez réessayer.')
                ->withInput();
        }
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(CorpsEtat $corpsEtat)
    {
        if (!Auth::user()->can('SYSTEM_CORPS_ETAT_EDIT')) {
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de modifier ce corps d’état.');
        }

        return Inertia::render('CorpsEtat/Edit', [
            'corpsEtat' => $corpsEtat,
        ]);
    }

    /**
     * Mettre à jour un corps d'état
     */
    public function update(Request $request, CorpsEtat $corpsEtat)
    {
        if (!Auth::user()->can('SYSTEM_CORPS_ETAT_EDIT')) {
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de modifier ce corps d’état.');
        }

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:corps_etat,code,' . $corpsEtat->id,
            'intitule' => 'required|string|max:255|unique:corps_etat,intitule,' . $corpsEtat->id,
            'ordre' => 'required|integer',
            'sous_total' => 'nullable|numeric|min:0',
        ], [
            'code.required' => 'Le code est obligatoire.',
            'code.unique' => 'Ce code existe déjà.',
            'intitule.required' => 'L’intitulé est obligatoire.',
            'intitule.unique' => 'Cet intitulé existe déjà.',
            'ordre.required' => 'L’ordre est obligatoire.',
            'ordre.integer' => 'L’ordre doit être un nombre entier.',
            'sous_total.numeric' => 'Le sous-total doit être un nombre.',
            'sous_total.min' => 'Le sous-total ne peut pas être négatif.',
        ]);

        try {
            $corpsEtat->update($validated);

            return redirect()
                ->route('corps-etat.index')
                ->with('success', 'Le corps d’état a été mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Une erreur est survenue lors de la mise à jour du corps d’état. Veuillez réessayer.')
                ->withInput();
        }
    }

    /**
     * Supprimer un corps d'état
     */
    public function destroy(CorpsEtat $corpsEtat)
    {
        if (!Auth::user()->can('SYSTEM_CORPS_ETAT_DELETE')) {
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de supprimer ce corps d’état.');
        }

        try {
            $corpsEtat->delete();

            return redirect()
                ->route('corps-etat.index')
                ->with('success', 'Le corps d’état a été supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Une erreur est survenue lors de la suppression du corps d’état. Veuillez réessayer.');
        }
    }
}
