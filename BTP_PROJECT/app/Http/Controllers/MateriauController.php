<?php

namespace App\Http\Controllers;

use App\Models\Materiau;
use App\Models\UniteMesure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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

    public function show($id)
    {
        if (!Auth::user()->can('SYSTEM_MATERIAU_VIEW')) {
            return back()->with(
                'error',
                "Vous n’avez pas l’autorisation de consulter ce matériel."
            );
        }

        $materiau = Materiau::with('unite', 'collectionsPrix')->findOrFail($id);

        return Inertia::render('Materiaux/Show', [
            'materiau' => $materiau,
        ]);
    }

    /**
     * Enregistrer un nouveau matériau
     */
    public function store(Request $request)
    {
        // 1. Vérification des permissions
        if (!Auth::user()->can('SYSTEM_MATERIAU_CREATE')) {
            return back()->with('error', "Vous n’avez pas l’autorisation de créer un matériel.");
        }

        // 2. Validation (sans champ code)
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:materiaux,nom',
            'unite_id' => 'required|exists:unites_mesure,id',
        ]);

        // 3. Génération automatique du code
        $nomClean = strtoupper(Str::ascii($validated['nom']));
        $letters = substr(preg_replace('/[^A-Z]/', '', $nomClean), 0, 3);
        $letters = str_pad($letters, 3, 'X'); // si moins de 3 lettres

        $prefix = "MAT-{$letters}-";

        do {
            $randomNumber = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $code = $prefix . $randomNumber;
        } while (Materiau::where('code', $code)->exists());

        // 4. Création
        Materiau::create([
            'code' => $code,
            'nom' => $validated['nom'],
            'unite_id' => $validated['unite_id'],
        ]);

        // 5. Redirection
        return redirect()
            ->route('materiaux.index')
            ->with('success', "Le matériel a été créé avec succès.");
    }

    public function update(Request $request, $id)
    {
        // 1. Vérification des permissions
        if (!Auth::user()->can('SYSTEM_MATERIAU_EDIT')) {
            return back()->with('error', "Vous n’avez pas l’autorisation de modifier ce matériel.");
        }

        $materiau = Materiau::findOrFail($id);

        // 2. Validation (sans champ code)
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:materiaux,nom,' . $materiau->id,
            'unite_id' => 'required|exists:unites_mesure,id',
        ]);

        // 3. Génération automatique du code
        $nomClean = strtoupper(Str::ascii($validated['nom']));
        $letters = substr(preg_replace('/[^A-Z]/', '', $nomClean), 0, 3);
        $letters = str_pad($letters, 3, 'X');

        $prefix = "MAT-{$letters}-";

        do {
            $randomNumber = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $newCode = $prefix . $randomNumber;
        } while (
            Materiau::where('code', $newCode)
                ->where('id', '!=', $materiau->id)
                ->exists()
        );

        // 4. Mise à jour
        $materiau->update([
            'code' => $newCode,
            'nom' => $validated['nom'],
            'unite_id' => $validated['unite_id'],
        ]);

        // 5. Redirection
        return redirect()
            ->route('materiaux.index')
            ->with('success', "Le matériel a été mis à jour avec succès et code recalculé.");
    }

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


