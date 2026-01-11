<?php

namespace App\Http\Controllers;

use App\Models\Batiment;
use App\Models\ComposantNiveau;
use App\Models\DevisEstimatif;
use App\Models\NiveauBatiment;
use App\Models\UniteMesure;
use App\Services\OrganisationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

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


    public function create(Batiment $batiment)
    {
       // $this->validateBatimentAccess($batiment, 'ORG_DEVIS_ESTIMATIF_VIEW');

        // Vérifier qu'il n'y a pas déjà un devis
        if ($batiment->devisEstimatif()->exists()) {
            return redirect()
                ->route('batiments.show', $batiment)
                ->with('error', 'Ce bâtiment possède déjà un devis estimatif.');
        }

        // Charger les données nécessaires
        $niveaux = NiveauBatiment::all();
        $unites  = UniteMesure::all();
        return Inertia::render('Organisations/DevisEstimatif/Create', [
            'batiment' => $batiment,
            'niveaux'  => $niveaux,
            'unites'   => $unites,
        ]);
    }


    public function store(Request $request, Batiment $batiment)
    {


       // $this->validateBatimentAccess($batiment, 'ORG_DEVIS_ESTIMATIF_VIEW');

        if ($batiment->devisEstimatif()->exists()) {
            return redirect()
                ->route('batiments.show', $batiment)
                ->with('error', 'Ce bâtiment possède déjà un devis estimatif.');
        }

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
                ->route('batiments.devis.store', $request->batiment_id)
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

   
    public function show(Batiment $batiment)
{

       //$this->validateBatimentAccess($batiment, 'ORG_DEVIS_ESTIMATIF_VIEW');

    $devis = $batiment->devisEstimatif;

    if (! $devis) {
        return redirect()
            ->route('batiments.show', $batiment)
            ->with('error', 'Ce bâtiment ne possède pas encore de devis estimatif.');
    }

    $devis->load([
        'batiment.projet.organisation',
        'composants.niveau',
        'composants.unite',
    ]);

    return Inertia::render('Organisations/DevisEstimatif/Show', [
        'batiment' => [
            'id' => $batiment->id,
            'nom' => $batiment->nom,
        ],

        'devis' => [
            'id' => $devis->id,
            'intitule' => $devis->intitule,
            'statut' => $devis->statut,

            'batiment' => [
                'nom' => $devis->batiment->nom,
                'projet' => [
                    'nom' => $devis->batiment->projet->nom,
                    'organisation' => [
                        'nom' => $devis->batiment->projet->organisation->nom,
                    ],
                ],
            ],

            'composants' => $devis->composants->map(fn ($c) => [
                'id' => $c->id,
                'code' => $c->code,
                'piece' => $c->piece,
                'qte' => $c->qte,
                'prix_unitaire' => $c->prix_unitaire,
                'montant' => $c->montant,

                'niveau' => [
                    'id' => $c->niveau->id,
                    'nom' => $c->niveau->libelle,
                ],

                'unite' => [
                    'id' => $c->unite->id,
                    'nom' => $c->unite->nom,
                ],
            ]),
        ],

        'totauxParNiveau' => $devis->totalParNiveau()->map(fn ($row) => [
            'niveau_id' => $row->niveau_id,
            'niveau_nom' => $row->niveau->nom,
            'total' => $row->total,
        ])->values(),

        'totalGeneral' => $devis->total(),
    ]);
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



    protected function validateBatimentAccess(Batiment $batiment, $permission): void
    {
        $activeOrganisationId = getPermissionsTeamId();

        // Vérifier permission
        if (!OrganisationContext::hasPermission(Auth::user(), $activeOrganisationId, $permission)) {
            abort(Response::HTTP_FORBIDDEN, "Vous n'avez pas cette permission");
        }

        // Vérifier projet
        if (!$batiment->projet) {
            abort(Response::HTTP_NOT_FOUND, 'Projet introuvable pour ce bâtiment.');
        }

        // Vérifier organisation
        if (!$batiment->projet->organisation) {
            abort(Response::HTTP_NOT_FOUND, 'Organisation introuvable.');
        }

        // Vérifier organisation active
        if ($batiment->projet->organisation->id !== $activeOrganisationId) {
            abort(Response::HTTP_FORBIDDEN, 'Accès refusé à ce bâtiment.');
        }
    }
}
