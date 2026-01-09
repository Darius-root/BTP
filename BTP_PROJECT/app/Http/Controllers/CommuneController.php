<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            return redirect()->back()->with('error', 'Permission refusée.');
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

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        if (!Auth::user()->can('SYSTEM_COMMUNE_CREATE')) {
            return redirect()->back()->with('error', 'Permission refusée.');
        }

        return Inertia::render('Communes/Create');
    }

    /**
     * Enregistrer une nouvelle commune
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('SYSTEM_COMMUNE_CREATE')) {
            return redirect()->back()->with('error', 'Permission refusée.');
        }

        $validated = $request->validate([
            'libelle' => 'required|string|max:255|unique:communes,libelle',
        ], [
            'libelle.required' => 'Le libellé est obligatoire.',
            'libelle.unique' => 'Cette commune existe déjà.',
        ]);

        $validated['code'] = $this->generateCommuneCode($validated['libelle']);

        Commune::create($validated);

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
            return redirect()->back()->with('error', 'Permission refusée.');
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
        if (!Auth::user()->can('SYSTEM_COMMUNE_EDIT')) {
            return redirect()->back()->with('error', 'Permission refusée.');
        }

        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:communes,code,' . $commune->id,
            'libelle' => 'required|string|max:255|unique:communes,libelle,' . $commune->id,
        ], [
            'libelle.unique' => 'Cette commune existe déjà.',
        ]);

        $commune->update($validated);

        return redirect()
            ->route('communes.index')
            ->with('success', 'Commune mise à jour avec succès.');
    }

    /**
     * Supprimer une commune
     */
    public function destroy(Commune $commune)
    {
        if (!Auth::user()->can('SYSTEM_COMMUNE_DELETE')) {
            return redirect()->back()->with('error', 'Permission refusée.');
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

    /**
     * Statistiques d’une commune
     */
    public function stats(Commune $commune)
    {
        if (!Auth::user()->can('SYSTEM_COMMUNE_VIEW')) {
            abort(403);
        }

        return response()->json([
            'arrondissements_count' => $commune->arrondissements()->count(),
            'collections_prix_count' => $commune->collectionsPrix()->count(),
            'arrondissements_with_collections' => $commune->arrondissements()
                ->whereHas('collectionsPrix')
                ->count(),
        ]);
    }

    /**
     * Prévisualisation du code généré
     */
    public function previewCode(Request $request)
    {
        if (!Auth::user()->can('SYSTEM_COMMUNE_VIEW')) {
            abort(403);
        }

        $libelle = $request->input('libelle');

        if (empty($libelle)) {
            return response()->json(['code' => '']);
        }

        return response()->json([
            'code' => $this->generateCommuneCode($libelle),
        ]);
    }

    /* ================== HELPERS ================== */

    private function generateCommuneCode($libelle)
    {
        $libelle = $this->removeAccents($libelle);
        $libelle = strtoupper(preg_replace('/[^A-Z0-9]/', '', $libelle));

        $baseCode = substr($libelle, 0, 4);
        $baseCode = strlen($baseCode) < 3 ? str_pad($baseCode, 3, 'X') : $baseCode;

        $code = $baseCode;
        $counter = 1;

        while (Commune::where('code', $code)->exists()) {
            $code = $baseCode . $counter++;
        }

        return $code;
    }

    private function removeAccents($string)
    {
        return strtr($string, [
            'À' => 'A',
            'Á' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Ä' => 'A',
            'Å' => 'A',
            'Ç' => 'C',
            'È' => 'E',
            'É' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Ì' => 'I',
            'Í' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ñ' => 'N',
            'Ò' => 'O',
            'Ó' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ù' => 'U',
            'Ú' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ñ' => 'n',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ÿ' => 'y'
        ]);
    }
}
