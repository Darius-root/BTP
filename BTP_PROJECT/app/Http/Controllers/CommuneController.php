<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use App\Models\Arrondissement;
use App\Models\CollectionPrix;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CommuneController extends Controller
{
    /**
     * Afficher la liste des communes
     */
    public function index(Request $request)
    {
        $query = Commune::withCount(['arrondissements', 'collectionsPrix']);

        // Recherche
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

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return Inertia::render('Communes/Create');
    }

    /**
     * Enregistrer une nouvelle commune
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'libelle' => [
                'required',
                'string',
                'max:255',
                'unique:communes,libelle',
            ],
        ], [
            'libelle.required' => 'Le libellé est obligatoire.',
            'libelle.unique' => 'Cette commune existe déjà.',
        ]);

        // Générer le code automatiquement à partir du libellé
        $validated['code'] = $this->generateCommuneCode($validated['libelle']);

        Commune::create($validated);

        return redirect()->route('communes.index')
            ->with('success', 'Commune créée avec succès.');
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Commune $commune)
    {
        return Inertia::render('Communes/Edit', [
            'commune' => $commune->loadCount(['arrondissements', 'collectionsPrix']),
        ]);
    }

    /**
     * Mettre à jour une commune
     */
    public function update(Request $request, Commune $commune)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:communes,code,' . $commune->id,
            'libelle' => [
                'required',
                'string',
                'max:255',
                'unique:communes,libelle,' . $commune->id,
            ],
        ], [
            'libelle.unique' => 'Cette commune existe déjà.',
        ]);

        $commune->update($validated);

        return redirect()->route('communes.index')
            ->with('success', 'Commune mise à jour avec succès.');
    }

    /**
     * Supprimer une commune avec ses arrondissements
     */
    public function destroy(Commune $commune)
    {
        // 1. Bloquer si la commune a des arrondissements
        if ($commune->arrondissements()->exists()) {
            return redirect()->route('communes.index')
                ->with('error', 'Impossible de supprimer cette commune car elle contient des arrondissements.');
        }

        // 2. Bloquer si la commune est utilisée dans des collections de prix
        if ($commune->collectionsPrix()->exists()) {
            return redirect()->route('communes.index')
                ->with('error', 'Impossible de supprimer cette commune car elle est utilisée dans des collections de prix.');
        }

        try {
            $commune->delete();

            return redirect()->route('communes.index')
                ->with('success', 'Commune supprimée avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('communes.index')
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Obtenir les statistiques d'une commune
     */
    public function stats(Commune $commune)
    {
        return response()->json([
            'arrondissements_count' => $commune->arrondissements()->count(),
            'collections_prix_count' => $commune->collectionsPrix()->count(),
            'arrondissements_with_collections' => $commune->arrondissements()
                ->whereHas('collectionsPrix')
                ->count(),
        ]);
    }

    /**
     * Générer un code unique à partir du libellé
     */
    private function generateCommuneCode($libelle)
    {
        // Nettoyer et normaliser le libellé
        $libelle = $this->removeAccents($libelle);
        $libelle = strtoupper($libelle);
        $libelle = preg_replace('/[^A-Z0-9]/', '', $libelle);

        // Extraire les 3-4 premières lettres
        $baseCode = substr($libelle, 0, 4);

        // Si le code de base est trop court, le compléter
        if (strlen($baseCode) < 3) {
            $baseCode = str_pad($baseCode, 3, 'X');
        }

        // Vérifier si le code existe déjà
        $code = $baseCode;
        $counter = 1;

        while (Commune::where('code', $code)->exists()) {
            // Si le code existe, ajouter un suffixe numérique
            $code = $baseCode . $counter;
            $counter++;
        }

        return $code;
    }

    /**
     * Retirer les accents d'une chaîne
     */
    private function removeAccents($string)
    {
        $unwanted_array = [
            'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A',
            'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E',
            'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
            'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y',
            'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a',
            'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i',
            'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y'
        ];

        return strtr($string, $unwanted_array);
    }

    /**
     * API pour prévisualiser le code généré à partir d'un libellé
     */
    public function previewCode(Request $request)
    {
        $libelle = $request->input('libelle');

        if (empty($libelle)) {
            return response()->json(['code' => '']);
        }

        $code = $this->generateCommuneCode($libelle);

        return response()->json(['code' => $code]);
    }
}
