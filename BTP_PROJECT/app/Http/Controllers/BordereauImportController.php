<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Bordereau;
use App\Models\BordereauLigne;
use App\Imports\BordereauImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class BordereauImportController extends Controller
{
    /**
     * Liste des bordereaux
     */
    public function index(Request $request)
    {
        $query = Bordereau::withCount('lignes')->orderBy('annee', 'desc');

        if ($request->filled('annee'))
            $query->where('annee', $request->annee);
        if ($request->filled('version'))
            $query->where('version', $request->version);

        return Inertia::render('Bordereaux/Index', [
            'bordereaux' => $query->paginate(15)->through(fn($bordereau) => [
                'id' => $bordereau->id,
                'libelle' => $bordereau->libelle,
                'annee' => $bordereau->annee,
                'version' => $bordereau->version,
                'lignes_count' => $bordereau->lignes_count,
            ]),
        ]);
    }

    /**
     * Formulaire d'import
     */
    public function create()
    {
        return Inertia::render('Bordereaux/Import');
    }

    /**
     * Import Excel
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240', // Max 10MB
            'annee' => 'required|integer|min:2000|max:' . (date('Y') + 10),
            'version' => 'required|string|max:10',
        ]);

        try {
            DB::beginTransaction();

            // Vérifier si un bordereau existe déjà pour cette année/version
            $existingCount = Bordereau::where('annee', $data['annee'])
                ->where('version', $data['version'])
                ->count();

            if ($existingCount > 0) {
                Log::info("Import dans un bordereau existant - Année: {$data['annee']}, Version: {$data['version']}");
            }

            // Importer le fichier
            Excel::import(
                new BordereauImport(
                    $data['annee'],
                    $data['version'],
                    Auth::id()
                ),
                $request->file('file')
            );

            DB::commit();

            return redirect()
                ->route('bordereaux.index')
                ->with('success', 'Bordereau importé avec succès. Les doublons ont été automatiquement ignorés ou mis à jour.');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            $failures = $e->failures();
            $errors = [];

            foreach ($failures as $failure) {
                $errors[] = "Ligne {$failure->row()}: " . implode(', ', $failure->errors());
            }

            Log::error('Erreur de validation Excel', ['errors' => $errors]);

            return back()
                ->withInput()
                ->withErrors([
                    'file' => 'Erreurs de validation dans le fichier: ' . implode(' | ', $errors)
                ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur lors de l\'import du bordereau', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'annee' => $data['annee'],
                'version' => $data['version'],
            ]);

            // Message plus explicite pour l'utilisateur
            $userMessage = $e->getMessage();

            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $userMessage = 'Le fichier contient des codes en double. Veuillez vérifier votre fichier Excel et supprimer les doublons.';
            }

            return back()
                ->withInput()
                ->withErrors([
                    'file' => 'Erreur lors de l\'import : ' . $userMessage
                ]);
        }
    }

    /**
     * Détail d'un bordereau
     */
    public function show(Bordereau $bordereau)
    {
        $bordereau->load('lignes');

        return Inertia::render('Bordereaux/Show', [
            'bordereau' => [
                'id' => $bordereau->id,
                'libelle' => $bordereau->libelle,
                'annee' => $bordereau->annee,
                'version' => $bordereau->version,
                'lignes' => $bordereau->lignes->map(fn($ligne) => [
                    'id' => $ligne->id,
                    'code' => $ligne->code,
                    'designation' => $ligne->designation,
                    'bi' => $ligne->bi,
                    'bs' => $ligne->bs,
                ]),
            ],
        ]);
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Bordereau $bordereau)
    {
        return Inertia::render('Bordereaux/Edit', [
            'bordereau' => [
                'id' => $bordereau->id,
                'libelle' => $bordereau->libelle,
                'annee' => $bordereau->annee,
                'version' => $bordereau->version,
            ],
        ]);
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, Bordereau $bordereau)
    {
        $data = $request->validate([
            'libelle' => 'required|string|max:255',
            'annee' => 'required|integer|min:2000',
            'version' => 'required|string|max:10',
        ]);

        $bordereau->update($data);

        return redirect()
            ->route('bordereaux.show', $bordereau)
            ->with('success', 'Bordereau mis à jour.');
    }

    /**
     * Suppression d'un bordereau et de ses lignes
     */
    public function destroy(Bordereau $bordereau)
    {
        $bordereau->delete();

        return redirect()
            ->route('bordereaux.index')
            ->with('success', 'Bordereau supprimé.');
    }

    /**
     * Suppression d'une ligne spécifique
     */
    public function destroyLigne(BordereauLigne $ligne)
    {
        $bordereauId = $ligne->bordereau_id;
        $ligne->delete();

        return redirect()
            ->route('bordereaux.show', $bordereauId)
            ->with('success', 'Ligne supprimée.');
    }
}
