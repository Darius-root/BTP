<?php

namespace App\Http\Controllers;

use App\Models\Bordereau;
use App\Imports\BordereauImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class BordereauImportController extends Controller
{
    /**
     * Liste des bordereaux
     */
    public function index(Request $request)
    {
        $query = Bordereau::withCount('designations')
            ->with('user:id,name')
            ->orderBy('created_at', 'desc');

        if ($request->filled('annee'))
            $query->where('annee', $request->annee);
        if ($request->filled('version'))
            $query->where('version', $request->version);
        if ($request->filled('nom_bordereau'))
            $query->where('nom_bordereau', 'like', '%' . $request->nom_bordereau . '%');

        // Récupérer toutes les années et versions disponibles
        $availableAnnees = Bordereau::select('annee')
            ->distinct()
            ->orderBy('annee', 'desc')
            ->pluck('annee')
            ->toArray();

        $availableVersions = Bordereau::select('version')
            ->distinct()
            ->orderBy('version', 'asc')
            ->pluck('version')
            ->toArray();

        return Inertia::render('Bordereaux/Index', [
            'bordereaux' => $query->paginate(15)->through(fn($b) => [
                'id' => $b->id,
                'nom_bordereau' => $b->nom_bordereau,
                'annee' => $b->annee,
                'version' => $b->version,
                'actif' => $b->actif, // Ajout du statut actif
                'designations_count' => $b->designations_count,
                'user_name' => $b->user->name ?? 'Inconnu',
                'created_at' => $b->created_at->format('d/m/Y H:i'),
            ]),
            'filters' => $request->only(['annee', 'version', 'nom_bordereau']),
            'availableAnnees' => $availableAnnees,
            'availableVersions' => $availableVersions,
        ]);
    }

    /**
     * Formulaire d'import
     */
    public function create()
    {
        // Années disponibles pour le select
        $annees = range(date('Y'), date('Y') - 10);

        return Inertia::render('Bordereaux/Import', [
            'annees' => $annees,
        ]);
    }

    /**
     * Import d'un seul fichier Excel
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
            'nom_bordereau' => 'required|string|max:255',
            'annee' => [
                'required',
                'string',
                'regex:/^[^\s]+$/',
                'max:10'
            ],
            'version' => [
                'required',
                'string',
                'regex:/^[^\s]+$/',
                'max:20'
            ],
        ], [
            'annee.regex' => 'L\'année ne doit pas contenir d\'espaces.',
            'version.regex' => 'La version ne doit pas contenir d\'espaces.',
        ]);

        try {
            DB::beginTransaction();

            // Vérifier si le bordereau existe déjà
            $exists = Bordereau::where([
                'nom_bordereau' => $data['nom_bordereau'],
                'annee' => $data['annee'],
                'version' => $data['version'],
            ])->exists();

            if ($exists) {
                return back()->withInput()->withErrors([
                    'file' => "Le bordereau '{$data['nom_bordereau']}' (Année: {$data['annee']}, Version: {$data['version']}) existe déjà."
                ]);
            }

            // Importer le fichier
            Excel::import(
                new BordereauImport(
                    $data['nom_bordereau'],
                    $data['annee'],
                    $data['version'],
                    Auth::id()
                ),
                $data['file']
            );

            DB::commit();

            return redirect()->route('bordereaux.index')
                ->with('success', "Bordereau importé avec succès.");

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();

            $validationErrors = [];
            foreach ($e->failures() as $failure) {
                $validationErrors[] = "Ligne {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return back()->withInput()->withErrors([
                'file' => "Erreurs de validation dans le fichier : " . implode(' | ', $validationErrors)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors([
                'file' => 'Erreur lors de l\'import : ' . $e->getMessage()
            ]);
        }
    }
    /**
     * Détail d'un bordereau
     */
    public function show($id)
    {
        $bordereau = Bordereau::with([
            'designations' => function ($query) {
                $query->orderBy('code');
            }
        ])->findOrFail($id);

        return Inertia::render('Bordereaux/Show', [
            'bordereau' => [
                'id' => $bordereau->id,
                'nom_bordereau' => $bordereau->nom_bordereau,
                'annee' => $bordereau->annee,
                'version' => $bordereau->version,
                'actif' => $bordereau->actif,
                'created_at' => $bordereau->created_at->format('d/m/Y H:i'),
                'designations' => $bordereau->designations->map(fn($d) => [
                    'id' => $d->id,
                    'code' => $d->code,
                    'designation' => $d->designation,
                    'caracteristiques' => $d->caracteristiques,
                    'caracteristiques_array' => $d->caracteristiques_formatted,
                    'unite_mesure' => $d->unite_mesure,
                    'bi' => number_format($d->bi, 2, ',', ' '),
                    'bs' => number_format($d->bs, 2, ',', ' '),
                ]),
            ],
        ]);
    }

    /**
     * Changer le statut actif/inactif d'un bordereau
     */
    public function toggleStatus($id)
    {
        try {
            DB::beginTransaction();

            $bordereau = Bordereau::findOrFail($id);

            // Si on veut activer ce bordereau (passer de false à true)
            if (!$bordereau->actif) {
                // Désactiver tous les autres bordereaux (les mettre à false)
                Bordereau::where('id', '!=', $id)->update(['actif' => false]);

                // Activer le bordereau courant
                $bordereau->actif = true;
                $bordereau->save();

                $message = 'Bordereau activé avec succès. Tous les autres bordereaux ont été désactivés.';
            } else {
                // Si on veut désactiver ce bordereau (passer de true à false)
                $bordereau->actif = false;
                $bordereau->save();

                $message = 'Bordereau désactivé avec succès.';
            }

            DB::commit();

            return redirect()->route('bordereaux.show', $bordereau->id)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors([
                'error' => 'Erreur lors du changement de statut : ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Supprimer un bordereau
     */
    public function destroy($id)
    {
        try {
            $bordereau = Bordereau::findOrFail($id);
            $bordereau->delete();

            return redirect()->route('bordereaux.index')
                ->with('success', 'Bordereau supprimé avec succès.');

        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ]);
        }
    }
}
