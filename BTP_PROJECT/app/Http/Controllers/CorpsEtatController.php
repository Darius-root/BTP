<?php

namespace App\Http\Controllers;

use App\Models\CorpsEtat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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

    public function show(CorpsEtat $corpsEtat)
    {
        if (!Auth::user()->can('SYSTEM_CORPS_ETAT_VIEW')) {
            return redirect()->back()->with('error', 'Vous n’avez pas la permission de consulter ce corps d’état.');
        }

        return Inertia::render('CorpsEtat/Show', [
            'corpsEtat' => $corpsEtat
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
            'intitule'   => 'required|string|max:255|unique:corps_etat,intitule',
            'ordre'      => 'required|integer',
            'sous_total' => 'nullable|numeric|min:0',
        ]);

        $intituleClean = strtoupper(Str::ascii($validated['intitule']));
        $words = preg_split('/\s+/', $intituleClean);

        $letters = '';
        if (count($words) > 1) {
            foreach ($words as $word) {
                if (strlen($word) <= 2) continue;
                $letters .= substr($word, 0, 1);
                if (strlen($letters) >= 3) break;
            }
        }
        if (strlen($letters) < 3) {
            $letters = str_pad(
                $letters,
                3,
                substr(preg_replace('/[^A-Z]/', '', $intituleClean), 0, 3 - strlen($letters)),
                STR_PAD_RIGHT
            );
        }
        $letters = substr($letters, 0, 3);

        $prefix = "COR-{$letters}-";

        do {
            $randomNumber = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $code = $prefix . $randomNumber;
        } while (CorpsEtat::where('code', $code)->exists());

        CorpsEtat::create([
            'code'       => $code,
            'intitule'   => $validated['intitule'],
            'ordre'      => $validated['ordre'],
            'sous_total' => $validated['sous_total'],
            'user_id'    => Auth::id(),
        ]);

        return redirect()
            ->route('corps-etat.index')
            ->with('success', 'Corps d’état créé avec succès.');
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
            'intitule'   => 'required|string|max:255|unique:corps_etat,intitule,' . $corpsEtat->id,
            'ordre'      => 'required|integer',
            'sous_total' => 'nullable|numeric|min:0',
        ]);

        $intituleClean = strtoupper(Str::ascii($validated['intitule']));
        $words = preg_split('/\s+/', $intituleClean);

        $letters = '';
        if (count($words) > 1) {
            foreach ($words as $word) {
                if (strlen($word) <= 2) continue;
                $letters .= substr($word, 0, 1);
                if (strlen($letters) >= 3) break;
            }
        }
        if (strlen($letters) < 3) {
            $letters = str_pad(
                $letters,
                3,
                substr(preg_replace('/[^A-Z]/', '', $intituleClean), 0, 3 - strlen($letters)),
                STR_PAD_RIGHT
            );
        }
        $letters = substr($letters, 0, 3);

        $prefix = "COR-{$letters}-";

        do {
            $randomNumber = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $newCode = $prefix . $randomNumber;
        } while (
            CorpsEtat::where('code', $newCode)
                ->where('id', '!=', $corpsEtat->id)
                ->exists()
        );

        $corpsEtat->update([
            'code'       => $newCode,
            'intitule'   => $validated['intitule'],
            'ordre'      => $validated['ordre'],
            'sous_total' => $validated['sous_total'],
        ]);

        return redirect()
            ->route('corps-etat.index')
            ->with('success', 'Corps d’état mis à jour avec succès et code recalculé.');
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



