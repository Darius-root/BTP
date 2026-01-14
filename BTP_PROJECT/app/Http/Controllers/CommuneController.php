<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CommuneController extends Controller
{
    /**
     * Afficher la liste des communes
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->can('SYSTEM_COMMUNE_VIEW')) {
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de consulter les communes.');
        }

        $query = Commune::withCount(['arrondissements', 'collectionsPrix']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', '%' . $request->search . '%')
                    ->orWhere('libelle', 'like', '%' . $request->search . '%');
            });
        }

        $communes = $query->orderBy('libelle')->paginate(15);

        return Inertia::render('Communes/Index', [
            'communes' => $communes,
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(Commune $commune)
    {
        // 1. Vérification des permissions
        if (!Auth::user()->can('SYSTEM_COMMUNE_VIEW')) {
            return redirect()->back()
                ->with('error', "Vous n'avez pas la permission de consulter cette commune.");
        }

        // 2. Charger la commune avec ses relations
        $commune->load([
            'arrondissements:id,code,libelle,commune_id',
        ])->loadCount(['arrondissements', 'collectionsPrix']);

        // 3. Retourner la vue Inertia
        return Inertia::render('Communes/Show', [
            'commune' => $commune,
        ]);
    }


    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        if (!Auth::user()->can('SYSTEM_COMMUNE_CREATE')) {
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de créer une commune.');
        }

        return Inertia::render('Communes/Create');
    }

    /**
     * Enregistrer une nouvelle commune
     */
    public function store(Request $request)
    {
        // 1. Vérification des permissions
        if (!Auth::user()->can('SYSTEM_COMMUNE_CREATE')) {
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de créer une commune.');
        }

        // 2. Validation des données
        $validated = $request->validate([
            'libelle' => 'required|string|max:255|unique:communes,libelle',
        ], [
            'libelle.required' => 'Le libellé de la commune est obligatoire.',
            'libelle.unique' => 'Une commune avec ce libellé existe déjà.',
        ]);

        // 3. Nettoyage du libellé
        $libelleClean = strtoupper(Str::ascii($validated['libelle']));
        $words = preg_split('/\s+/', $libelleClean);

        // 4. Extraction des lettres
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
            $letters = str_pad(
                $letters,
                3,
                substr(preg_replace('/[^A-Z]/', '', $libelleClean), 0, 3 - strlen($letters)),
                STR_PAD_RIGHT
            );
        }

        $letters = substr($letters, 0, 3);

        // 5. Préfixe du code
        $prefix = "COM-{$letters}-";

        // 6. Générer un nombre aléatoire à 3 chiffres et vérifier unicité
        do {
            $randomNumber = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $code = $prefix . $randomNumber;
        } while (Commune::where('code', $code)->exists());

        // 7. Création de la commune
        Commune::create([
            'code' => $code,
            'libelle' => $validated['libelle'],
        ]);

        // 8. Redirection
        return redirect()
            ->route('communes.index')
            ->with('success', 'Commune créée avec succès.');
    }

    /**
     * Afficher le formulaire d’édition
     */
    public function edit(Commune $commune)
    {
        if (!Auth::user()->can('SYSTEM_COMMUNE_EDIT')) {
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de modifier cette commune.');
        }

        return Inertia::render('Communes/Edit', [
            'commune' => $commune->loadCount(['arrondissements', 'collectionsPrix']),
        ]);
    }

    /**
     * Mettre à jour une commune
     */
    public function update(Request $request, Commune $commune)
    {
        // 1. Vérification des permissions
        if (!Auth::user()->can('SYSTEM_COMMUNE_EDIT')) {
            return redirect()->back()
                ->with('error', 'Vous n’avez pas la permission de modifier cette commune.');
        }

        // 2. Validation
        $validated = $request->validate([
            'libelle' => 'required|string|max:255',
        ], [
            'libelle.required' => 'Le libellé est obligatoire.',
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
            $letters = str_pad(
                $letters,
                3,
                substr(preg_replace('/[^A-Z]/', '', $libelleClean), 0, 3 - strlen($letters)),
                STR_PAD_RIGHT
            );
        }

        $letters = substr($letters, 0, 3); // exactement 3 lettres

        // 5. Préfixe du code
        $prefix = "COM-{$letters}-";

        // 6. Génération de 3 chiffres aléatoires uniques
        do {
            $randomNumber = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $newCode = $prefix . $randomNumber;
        } while (Commune::where('code', $newCode)->where('id', '!=', $commune->id)->exists());

        // 7. Mise à jour de la commune
        $commune->update([
            'libelle' => $validated['libelle'],
            'code' => $newCode,
        ]);

        // 8. Redirection
        return redirect()
            ->route('communes.index')
            ->with('success', 'Commune mise à jour avec succès et code recalculé.');
    }

    /**
     * Supprimer une commune
     */
    public function destroy(Commune $commune)
    {
        if (!Auth::user()->can('SYSTEM_COMMUNE_DELETE')) {
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de supprimer cette commune.');
        }

        if ($commune->arrondissements()->exists()) {
            return redirect()->route('communes.index')
                ->with('error', 'Impossible de supprimer cette commune car elle contient des arrondissements.');
        }

        if ($commune->collectionsPrix()->exists()) {
            return redirect()->route('communes.index')
                ->with('error', 'Impossible de supprimer cette commune car elle est utilisée dans des collections de prix.');
        }

        $commune->delete();

        return redirect()
            ->route('communes.index')
            ->with('success', 'Commune supprimée avec succès.');
    }
}
