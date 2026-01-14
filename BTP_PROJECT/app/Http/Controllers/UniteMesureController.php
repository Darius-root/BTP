<?php

namespace App\Http\Controllers;

use App\Models\UniteMesure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Throwable;

class UniteMesureController extends Controller
{
    /**
     * Afficher la liste des unités de mesure
     */
    public function index()
    {
        try {
            if (!Auth::user()->can('SYSTEM_UNITE_MESURE_VIEW')) {
                return back()->with('error', "Vous n'avez pas la permission de voir les unités de mesure.");
            }

            $unites = UniteMesure::orderBy('code')->get();

            return Inertia::render('UnitesMesure/Index', [
                'unites' => $unites,
            ]);
        } catch (Throwable $e) {
            Log::error('Erreur index unités de mesure: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement des unités de mesure.');
        }
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        try {
            if (!Auth::user()->can('SYSTEM_UNITE_MESURE_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer une unité de mesure.");
            }

            return Inertia::render('UnitesMesure/Create');
        } catch (Throwable $e) {
            Log::error('Erreur create unité de mesure: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'accès au formulaire.');
        }
    }

    public function show(UniteMesure $uniteMesure)
    {
        if (!Auth::user()->can('SYSTEM_UNITE_MESURE_VIEW')) {
            return back()->with('error', "Vous n'avez pas la permission de voir cette unité de mesure.");
        }
        $uniteMesure->load('materiaux');
        return Inertia::render('UnitesMesure/Show', [
            'uniteMesure' => $uniteMesure,
        ]);
    }

    /**
     * Enregistrer une nouvelle unité de mesure
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('SYSTEM_UNITE_MESURE_CREATE')) {
            return back()->with('error', "Vous n’avez pas l’autorisation de créer une unité de mesure.");
        }

        $validated = $request->validate([
            'libelle' => 'required|string|max:255|unique:unites_mesure,libelle',
        ]);

        // Génération automatique du code
        $libelleClean = strtoupper(Str::ascii($validated['libelle']));
        $letters = substr(preg_replace('/[^A-Z]/', '', $libelleClean), 0, 3);
        $letters = str_pad($letters, 3, 'X'); // si moins de 3 lettres

        $prefix = "UNI-{$letters}-";

        do {
            $randomNumber = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $code = $prefix . $randomNumber;
        } while (UniteMesure::where('code', $code)->exists());

        UniteMesure::create([
            'code' => $code,
            'libelle' => $validated['libelle'],
        ]);

        return redirect()
            ->route('unites-mesure.index')
            ->with('success', "L’unité de mesure a été créée avec succès.");
    }

    /**
     * Mettre à jour une unité de mesure
     */
    public function update(Request $request, UniteMesure $unite)
    {
        if (!Auth::user()->can('SYSTEM_UNITE_MESURE_EDIT')) {
            return back()->with('error', "Vous n’avez pas l’autorisation de modifier cette unité de mesure.");
        }

        $validated = $request->validate([
            'libelle' => 'required|string|max:255|unique:unites_mesure,libelle,' . $unite->id,
        ]);

        // Génération automatique du code
        $libelleClean = strtoupper(Str::ascii($validated['libelle']));
        $letters = substr(preg_replace('/[^A-Z]/', '', $libelleClean), 0, 3);
        $letters = str_pad($letters, 3, 'X');

        $prefix = "UNI-{$letters}-";

        do {
            $randomNumber = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $newCode = $prefix . $randomNumber;
        } while (
            UniteMesure::where('code', $newCode)
                ->where('id', '!=', $unite->id)
                ->exists()
        );

        $unite->update([
            'code' => $newCode,
            'libelle' => $validated['libelle'],
        ]);

        return redirect()
            ->route('unites-mesure.index')
            ->with('success', "L’unité de mesure a été mise à jour avec succès et code recalculé.");
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(UniteMesure $uniteMesure)
    {
        try {
            if (!Auth::user()->can('SYSTEM_UNITE_MESURE_EDIT')) {
                return back()->with('error', "Vous n'avez pas la permission de modifier cette unité de mesure.");
            }

            return Inertia::render('UnitesMesure/Edit', [
                'unite' => $uniteMesure,
            ]);
        } catch (Throwable $e) {
            Log::error('Erreur edit unité de mesure: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'accès à l\'unité de mesure.');
        }
    }

    /**
     * Supprimer une unité de mesure
     */
    public function destroy(UniteMesure $uniteMesure)
    {
        try {
            if (!Auth::user()->can('SYSTEM_UNITE_MESURE_DELETE')) {
                return back()->with('error', "Vous n'avez pas la permission de supprimer cette unité de mesure.");
            }

            // Vérifier si l'unité de mesure est utilisée dans des matériaux
            if ($uniteMesure->materiaux()->exists()) {
                return back()->with('error', 'Impossible de supprimer cette unité de mesure car elle est utilisée dans des matériaux.');
            }

            $uniteMesure->delete();

            return redirect()->route('unites-mesure.index')
                ->with('success', 'Unité de mesure supprimée avec succès.');
        } catch (Throwable $e) {
            Log::error('Erreur destroy unité de mesure: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la suppression de l\'unité de mesure.');
        }
    }
}
