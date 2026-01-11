<?php

namespace App\Http\Controllers;

use App\Models\Batiment;
use App\Models\ComposantNiveau;
use App\Models\DevisEstimatif;
use App\Models\NiveauBatiment;
use App\Models\UniteMesure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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

        $request->validate(
            [
                'intitule' => 'required|string|max:255',
                'batiment_id' => [
                    'required',
                    'exists:batiments,id',
                    Rule::unique('devis_estimatif', 'batiment_id'),
                ],

                'niveaux' => 'required|array|min:1',

                'niveaux.*.niveau_id' => 'required|exists:niveaux_batiment,id',
                'niveaux.*.composants' => 'required|array|min:1',

                'niveaux.*.composants.*.code' => 'required|string|max:50',
                'niveaux.*.composants.*.piece' => 'required|string|max:255',
                'niveaux.*.composants.*.unite_id' => 'required|exists:unites_mesure,id',
                'niveaux.*.composants.*.qte' => 'required|numeric|min:0',
                'niveaux.*.composants.*.prix_unitaire' => 'required|numeric|min:0',
            ],
            [
                // Intitulé
                'intitule.required' => 'Veuillez renseigner l’intitulé du devis.',
                'intitule.string'   => 'L’intitulé doit être une chaîne de caractères.',
                'intitule.max'      => 'L’intitulé ne doit pas dépasser :max caractères.',

                // Bâtiment
                'batiment_id.exists' => 'Le bâtiment sélectionné est invalide.',

                // Niveaux
                'niveaux.required' => 'Vous devez ajouter au moins un niveau.',
                'niveaux.array'    => 'Les niveaux doivent être envoyés sous forme de tableau.',
                'niveaux.min'      => 'Ajoutez au moins :min niveau.',

                'niveaux.*.niveau_id.required' => 'Veuillez sélectionner un niveau.',
                'niveaux.*.niveau_id.exists'   => 'Le niveau choisi n’existe pas.',

                'niveaux.*.composants.required' => 'Chaque niveau doit contenir au moins un composant.',
                'niveaux.*.composants.array'    => 'Les composants doivent être envoyés sous forme de tableau.',
                'niveaux.*.composants.min'      => 'Ajoutez au moins :min composant.',

                // Composants
                'niveaux.*.composants.*.code.required' => 'Le code du composant est obligatoire.',
                'niveaux.*.composants.*.code.string'   => 'Le code doit être une chaîne de caractères.',
                'niveaux.*.composants.*.code.max'      => 'Le code ne doit pas dépasser :max caractères.',

                'niveaux.*.composants.*.piece.required' => 'Le nom de la pièce est obligatoire.',
                'niveaux.*.composants.*.piece.string'   => 'La pièce doit être une chaîne de caractères.',
                'niveaux.*.composants.*.piece.max'      => 'Le nom de la pièce ne doit pas dépasser :max caractères.',

                'niveaux.*.composants.*.unite_id.required' => 'Veuillez sélectionner une unité de mesure.',
                'niveaux.*.composants.*.unite_id.exists'   => 'L’unité de mesure choisie n’existe pas.',

                'niveaux.*.composants.*.qte.required' => 'La quantité est obligatoire.',
                'niveaux.*.composants.*.qte.numeric'  => 'La quantité doit être un nombre.',
                'niveaux.*.composants.*.qte.min'      => 'La quantité doit être au minimum :min.',

                'niveaux.*.composants.*.prix_unitaire.required' => 'Le prix unitaire est obligatoire.',
                'niveaux.*.composants.*.prix_unitaire.numeric'  => 'Le prix unitaire doit être un nombre.',
                'niveaux.*.composants.*.prix_unitaire.min'      => 'Le prix unitaire doit être au minimum :min.',
            ]
        );

        dd($request->all());

        $batiment = Batiment::with('projet.organisation')->findOrFail(
            $request->batiment_id
        );

        // Organisation active
        if (
            session('active_organisation_id') !==
            $batiment->projet->organisation_id
        ) {
            abort(403, "Organisation invalide");
        }

        // Sécurité utilisateur
        if (! auth()->user()
            ->organisations
            ->contains($batiment->projet->organisation_id)) {
            abort(403, "Accès refusé");
        }


        return DB::transaction(function () use ($request) {

            // =============================
            // 1️ Création du devis
            // =============================
            $devis = DevisEstimatif::create([
                'intitule' => $request->intitule,
                'batiment_id' => $request->batiment_id,
                'code' => $this->generateCode(),
                'statut' => 'brouillon',
                'is_template' => false,
                'created_by' => Auth::user()->id,
            ]);

            // Pour vérifier unicité code ASxx par devis
            $codesUtilises = [];

            // =============================
            // 2️ Boucle niveaux & composants
            // =============================
            foreach ($request->niveaux as $niveau) {

                foreach ($niveau['composants'] as $comp) {

                    //  Sécurité métier : code unique par devis
                    if (in_array($comp['code'], $codesUtilises)) {
                        throw ValidationException::withMessages([
                            'code' => "Le code {$comp['code']} est déjà utilisé dans ce devis."
                        ]);
                    }

                    $codesUtilises[] = $comp['code'];

                    //  Calcul sécurisé du montant
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
