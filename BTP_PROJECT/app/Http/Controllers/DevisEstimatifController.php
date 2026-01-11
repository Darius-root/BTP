<?php

namespace App\Http\Controllers;

use App\Models\ComposantNiveau;
use App\Models\DevisEstimatif;
use App\Models\NiveauBatiment;
use App\Models\UniteMesure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class DevisEstimatifController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $niveaux = NiveauBatiment::all();      // niveaux disponibles
        $unites = UniteMesure::all();  
      
        // unités de mesure
        return Inertia::render('Organisations/DevisEstimatif/Create', [
            'niveaux' => $niveaux,
            'unites' => $unites,
        ]);
    }


    public function store(Request $request)
    {

    dd($request->all());
        $request->validate([
            'intitule' => 'required|string|max:255',
            'batiment_id' => 'nullable|exists:batiments,id',

            'niveaux' => 'required|array|min:1',

            'niveaux.*.niveau_id' => 'required|exists:niveaux_batiment,id',
            'niveaux.*.composants' => 'required|array|min:1',

            'niveaux.*.composants.*.code' => 'required|string|max:50',
            'niveaux.*.composants.*.piece' => 'required|string|max:255',
            'niveaux.*.composants.*.unite_id' => 'required|exists:unites_mesure,id',
            'niveaux.*.composants.*.qte' => 'required|numeric|min:0',
            'niveaux.*.composants.*.prix_unitaire' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {

            // =============================
            // 1️⃣ Création du devis
            // =============================
            $devis = DevisEstimatif::create([
                'intitule' => $request->intitule,
                'batiment_id' => $request->batiment_id,
                'code' => $this->generateCode(),
                'statut' => 'brouillon',
                'is_template' => false,
                'created_by' => auth()->id(),
            ]);

            // Pour vérifier unicité code ASxx par devis
            $codesUtilises = [];

            // =============================
            // 2️⃣ Boucle niveaux & composants
            // =============================
            foreach ($request->niveaux as $niveau) {

                foreach ($niveau['composants'] as $comp) {

                    // 🔐 Sécurité métier : code unique par devis
                    if (in_array($comp['code'], $codesUtilises)) {
                        throw ValidationException::withMessages([
                            'code' => "Le code {$comp['code']} est déjà utilisé dans ce devis."
                        ]);
                    }

                    $codesUtilises[] = $comp['code'];

                    // 🔢 Calcul sécurisé du montant
                    $montant = bcmul($comp['qte'], $comp['prix_unitaire'], 2);

                    ComposantNiveau::create([
                        'devis_estimatif_id' => $devis->id,
                        'niveau_id' => $niveau['niveau_id'],
                        'code' => $comp['code'],
                        'piece' => $comp['piece'],
                        'unite_id' => $comp['unite_id'],
                        'qte' => $comp['qte'],
                        'prix_unitaire' => $comp['prix_unitaire'],
                        'montant' => $montant,
                    ]);
                }
            }

            return redirect()
                ->route('devis.show', $devis->id)
                ->with('success', 'Devis estimatif créé avec succès.');
        });
    }

    /**
     * Génération automatique du code devis
     * Exemple : DEV-2026-0001
     */
    private function generateCode(): string
    {
        $lastId = DevisEstimatif::max('id') ?? 0;
        return 'DEV-' . date('Y') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
