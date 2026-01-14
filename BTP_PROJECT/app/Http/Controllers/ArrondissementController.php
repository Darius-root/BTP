<?php

namespace App\Http\Controllers;

use App\Models\Arrondissement;
use App\Models\Commune;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use function intval;

class ArrondissementController extends Controller
{
    /**
     * Afficher la liste des arrondissements
     */
    public function index()
    {


        if (!Auth::user()->can('SYSTEM_ARRONDISSEMENT_VIEW')) {
            return redirect()->back()->with('error', "Vous n'avez pas la permission de consulter à la liste des arrondissements");
        }

        $arrondissements = Arrondissement::with('commune')
            ->orderBy('code')
            ->get();

        return Inertia::render('Arrondissements/Index', [
            'arrondissements' => $arrondissements,
        ]);
    }

    public function show(Arrondissement $arrondissement)
    {
        if (!Auth::user()->can('SYSTEM_ARRONDISSEMENT_VIEW')) {
            return redirect()->back()->with('error', "Vous n'avez pas la permission de consulter cet arrondissement");
        }
        $arrondissement->load('commune');

        return Inertia::render('Arrondissements/Show', [
            'arrondissement' => $arrondissement,
        ]);
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        if (!Auth::user()->can('SYSTEM_ARRONDISSEMENT_CREATE')) {
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de créer un arrondissement.');
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
        // 1. Vérification des permissions
        if (!Auth::user()->can('SYSTEM_ARRONDISSEMENT_CREATE')) {
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de créer un arrondissement.');
        }

        // 2. Validation des données
        $validated = $request->validate([
            'libelle' => 'required|string|max:255|unique:arrondissements,libelle',
            'commune_id' => 'required|exists:communes,id',
        ]);

        // 3. Nettoyage du libellé pour générer les lettres
        $libelleClean = strtoupper(Str::ascii($validated['libelle']));
        $words = preg_split('/\s+/', $libelleClean);

        // 4. Extraction des 3 lettres pour le code
        $letters = '';

        if (count($words) > 1) {
            foreach ($words as $word) {
                if (strlen($word) <= 2)
                    continue; // ignore mots très courts
                $letters .= substr($word, 0, 1);
                if (strlen($letters) >= 3)
                    break;
            }
        }

        if (strlen($letters) < 3) {
            $letters = str_pad($letters, 3, substr(preg_replace('/[^A-Z]/', '', $libelleClean), 0, 3 - strlen($letters)), STR_PAD_RIGHT);
        }

        $letters = substr($letters, 0, 3);

        // 5. Préfixe du code
        $prefix = "ARR-{$letters}-";

        // 6. Générer un nombre aléatoire à 3 chiffres et vérifier unicité
        do {
            $randomNumber = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $code = $prefix . $randomNumber;
        } while (Arrondissement::where('code', $code)->exists()); // s'assure que le code est unique

        // 7. Création de l'arrondissement
        Arrondissement::create([
            'code' => $code,
            'libelle' => $validated['libelle'],
            'commune_id' => $validated['commune_id'],
        ]);

        // 8. Redirection
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
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de modifier cet arrondissement.');
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
        // 1. Vérification des permissions
        if (!Auth::user()->can('SYSTEM_ARRONDISSEMENT_EDIT')) {
            return redirect()->back()
                ->with('error', 'Vous n’avez pas la permission de modifier cet arrondissement.');
        }

        // 2. Validation
        $validated = $request->validate([
            'libelle' => 'required|string|max:255',
            'commune_id' => 'required|exists:communes,id',
        ]);

        // 3. Nettoyage du libellé pour générer les lettres
        $libelleClean = strtoupper(Str::ascii($validated['libelle']));
        $words = preg_split('/\s+/', $libelleClean);

        // 4. Extraction des 3 lettres pour le code
        $letters = '';

        if (count($words) > 1) {
            foreach ($words as $word) {
                if (strlen($word) <= 2)
                    continue; // ignorer les petits mots
                $letters .= substr($word, 0, 1);
                if (strlen($letters) >= 3)
                    break;
            }
        }

        if (strlen($letters) < 3) {
            $letters = str_pad($letters, 3, substr(preg_replace('/[^A-Z]/', '', $libelleClean), 0, 3 - strlen($letters)), STR_PAD_RIGHT);
        }

        $letters = substr($letters, 0, 3); // exactement 3 lettres

        // 5. Préfixe du code
        $prefix = "ARR-{$letters}-";

        // 6. Génération de 3 chiffres aléatoires uniques
        do {
            $randomNumber = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $newCode = $prefix . $randomNumber;
        } while (Arrondissement::where('code', $newCode)->where('id', '!=', $arrondissement->id)->exists());

        // 7. Mise à jour de l'arrondissement
        $arrondissement->update([
            'libelle' => $validated['libelle'],
            'commune_id' => $validated['commune_id'],
            'code' => $newCode,
        ]);

        // 8. Redirection
        return redirect()
            ->route('arrondissements.index')
            ->with('success', 'Arrondissement mis à jour avec succès et code recalculé.');
    }

    /**
     * Supprimer un arrondissement
     */
    public function destroy(Arrondissement $arrondissement)
    {
        if (!Auth::user()->can('SYSTEM_ARRONDISSEMENT_DELETE')) {
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de supprimer cet arrondissement.');
        }

        $arrondissement->delete();

        return redirect()
            ->route('arrondissements.index')
            ->with('success', 'Arrondissement supprimé avec succès.');
    }
}
