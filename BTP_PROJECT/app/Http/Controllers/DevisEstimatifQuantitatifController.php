<?php

namespace App\Http\Controllers;

use App\Models\Batiment;
use App\Models\CorpsEtat;
use App\Models\DevisEstimatifQuantitatif;
use App\Models\DevisLot;
use App\Models\LotComposant;
use App\Models\UniteMesure;
use App\Services\OrganisationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class DevisEstimatifQuantitatifController extends Controller
{
    /**
     * Formulaire de création
     */
    public function create(Batiment $batiment)
    {
        //  Sécurité métier
        $this->validateBatimentAccess($batiment, 'ORG_DEVIS_QUANTITATIF_CREATE');


        if ($batiment->devisEstimatifQuantitatif()->exists()) {
            return redirect()->route('batiments.devis-estimatif-quantitatif.editCorpsEtat', ['batiment' => $batiment->id, 'devis_estimatif_quantitatif' => $batiment->devisEstimatifQuantitatif->id,])->with('error', 'Ce bâtiment possède déjà un devis. Vous avez été redirigé vers son édition.');
        }
        return Inertia::render('Organisations/DevisEstimatifQte/Create', [
            'batiment' => [
                'id'   => $batiment->id,
                'nom'  => $batiment->nom,
                'code' => $batiment->code,
            ],

        ]);
    }

    /**
     * Enregistrement
     */
    public function store(Request $request, Batiment $batiment)
    {
        $this->validateBatimentAccess(
            $batiment,
            'ORG_DEVIS_QUANTITATIF_CREATE'
        );

        if ($batiment->devisEstimatifQuantitatif()->exists()) {
            return redirect()->route('batiments.devis-estimatif-quantitatif.editCorpsEtat', ['batiment' => $batiment->id, 'devis_estimatif_quantitatif' => $batiment->devisEstimatifQuantitatif->id,])->with('error', 'Ce bâtiment possède déjà un devis. Vous avez été redirigé vers son édition.');
        }

        $data = $request->validate([
            'intitule' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:devis_estimatif_quantitatif,code',
        ]);

        $devis = DevisEstimatifQuantitatif::create([
            'batiment_id' => $batiment->id,
            'intitule' => $data['intitule'],
            'code' => $data['code'],
            'statut' => 'brouillon',
            'is_template' => false,
            'created_by' => Auth::user()->id,
        ]);

        //  Redirection vers l’édition (corps d’état / lots)
        return redirect()
            ->route('batiments.devis-estimatif-quantitatif.show', [
                'batiment' => $batiment->id,
                'devis_estimatif_quantitatif' => $devis->id,
            ])
            ->with('success', 'Devis créé. Vous pouvez maintenant saisir les quantités.');
    }


    public function edit(Batiment $batiment)
    {
        //  Sécurité d’accès
        $this->validateBatimentAccess(
            $batiment,
            'ORG_DEVIS_QUANTITATIF_EDIT'
        );


        // Le bâtiment n’a qu’un seul devis
        $devis = $batiment->devisEstimatifQuantitatif()->firstOrFail();
        if (!$devis) {
            return redirect()
                ->route('batiments.show', $batiment)
                ->with('error', 'Aucun devis trouvé pour ce bâtiment.');
        }
        if ($devis->statut === 'valide') {

            return back()->with([
                'error' => 'Un devis validé ne peut pas être supprimé.',
            ]);
        }
        return Inertia::render('Organisations/DevisEstimatifQte/Edit', [
            'batiment' => [
                'id' => $batiment->id,
                'nom' => $batiment->nom,
            ],
            'devis' => [
                'id' => $devis->id,
                'intitule' => $devis->intitule,
                'code' => $devis->code,
                'statut' => $devis->statut,
            ],
        ]);
    }

    public function update(Request $request, Batiment $batiment)
    {

        //  Sécurité d’accès (optionnel)
        $this->validateBatimentAccess($batiment, 'ORG_DEVIS_QUANTITATIF_EDIT');

        // On récupère le devis existant
        $devis = $batiment->devisEstimatifQuantitatif()->firstOrFail();
        if ($devis->statut === 'valide') {
            return back()->with([
                'error' => 'Un devis validé ne peut pas être supprimé.',
            ]);
        }
        // Validation
        $data = $request->validate([
            'intitule' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:devis_estimatif_quantitatif,code,' . $devis->id,

        ]);

        // Mise à jour
        $devis->update([
            'intitule' => $data['intitule'],
            'code' => $data['code'],
            'updated_by' => Auth::user()->id,
        ]);

        return redirect()
            ->route('batiments.devis-estimatif-quantitatif.show', ['batiment' => $batiment->id, 'devis_estimatif_quantitatif' => $devis])
            ->with('success', 'Devis mis à jour avec succès.');
    }

    /**
     * Affichage
     */
    public function show(Batiment $batiment)
    {

        //  Sécurité d’accès (optionnel)
        $this->validateBatimentAccess(
            $batiment,
            'ORG_DEVIS_QUANTITATIF_VIEW'
        );

        $devis = $batiment->devisEstimatifQuantitatif()
            ->with([
                'lots.composants.unite',
                'lots.corpsEtat',
            ])
            ->first();

              
        if (!$devis) {
            return redirect()
                ->route('batiments.show', $batiment)
                ->with('error', 'Aucun devis trouvé pour ce bâtiment.');
        }
        /**
         * Tous les corps d’état,
         * même ceux sans lots
         */
        $corpsEtats = CorpsEtat::orderBy('ordre')->get()->map(function ($ce) use ($devis) {
            $lots = $devis->lots
                ->where('corps_etat_id', $ce->id)
                ->values()
                ->map(fn($lot) => [
                    'code' => $lot->code,
                    'intitule' => $lot->intitule,
                    'composants' => $lot->composants->map(fn($c) => [
                        'designation' => $c->designation,
                        'quantite' => $c->quantite,
                        'prix_unitaire' => $c->prix_unitaire,
                        'unite' => [
                            'code' => $c->unite->code ?? null,
                        ],
                    ]),
                ]);

            return [
                'id' => $ce->id,
                'intitule' => $ce->intitule,
                'lots' => $lots,
            ];
        });

        return Inertia::render('Organisations/DevisEstimatifQte/Show', [
            'batiment' => [
                'id' => $batiment->id,
                'nom' => $batiment->nom,
            ],
            'devis' => [
                'id' => $devis->id,
                'intitule' => $devis->intitule,
                'statut' => $devis->statut,
            ],
            'corpsEtats' => $corpsEtats,
            'devise' => $batiment->projet->devise['libelle'] ?? '__',
        ]);
    }

    /**
     * Edition
     */
    public function editCorpsEtat(Batiment $batiment,  $devis)
    {
        $this->validateBatimentAccess(
            $batiment,
            'ORG_DEVIS_QUANTITATIF_EDIT'
        );

             $devis = DevisEstimatifQuantitatif::find($devis);

        if (!$devis) {
            return redirect()
                ->route('batiments.show', $batiment)
                ->with('error', 'Aucun devis trouvé pour ce bâtiment.');
        }
        if ($devis->statut === 'valide') {
            return back()->with([
                'error' => 'Un devis validé ne peut pas être supprimé.',
            ]);
        }

        $devis->load([
            'lots.composants.unite',
            'lots.corpsEtat',
        ]);
        $corpsEtats = CorpsEtat::orderBy('ordre')
            ->with([
                'lots' => function ($q) use ($devis) {
                    $q->where('devis_id', $devis->id)
                        ->with('composants');
                }
            ])
            ->get();



        return Inertia::render('Organisations/DevisEstimatifQte/EditCorpsEtat', [
            'batiment' => [
                'id' => $batiment->id,
                'nom' => $batiment->nom,
            ],
            'devis' => [
                'id' => $devis->id,
                'intitule' => $devis->intitule,
                'statut' => $devis->statut,
            ],
            'corpsEtats' => $corpsEtats,
            'unites' => UniteMesure::orderBy('libelle')->get(),
            'lots' => $devis->lots,
            'devise' => $batiment->projet->devise['libelle'] ?? '__'

        ]);
    }



    public function updateCorpsEtat(Request $request, Batiment $batiment, $devis)
    {
 $this->validateBatimentAccess(
            $batiment,
            'ORG_DEVIS_QUANTITATIF_EDIT'
        );


        $devis = DevisEstimatifQuantitatif::find($devis);
        if ($devis->statut === 'valide') {
            return back()->with([
                'error' => 'Un devis validé ne peut pas être supprimé.',
            ]);
        }
        $data = $request->validate(
            [
                'corps_etat_id' => ['required', 'exists:corps_etat,id'],

                'lots' => ['required', 'array', 'min:1'],

                'lots.*.code' => ['required', 'string', 'max:50'],
                'lots.*.intitule' => ['required', 'string', 'max:255'],

                'lots.*.composants' => ['required', 'array', 'min:1'],

                'lots.*.composants.*.designation' => ['required', 'string', 'max:255'],

                'lots.*.composants.*.unite_id' => ['required', 'integer', 'exists:unites_mesure,id'],
                'lots.*.composants.*.quantite' => ['required', 'numeric', 'gt:0'],
                'lots.*.composants.*.prix_unitaire' => ['required', 'numeric', 'min:0'],
            ],
            [
                // Corps d’état
                'corps_etat_id.required' => 'Veuillez sélectionner un corps d’état',
                'corps_etat_id.exists'   => 'Corps d’état invalide',

                // Lots
                'lots.required' => 'Au moins un lot est requis',
                'lots.array'    => 'Le format des lots est invalide',
                'lots.min'      => 'Vous devez ajouter au moins un lot',

                // Code lot
                'lots.*.code.required' => 'Le code du lot est obligatoire',
                'lots.*.code.string'   => 'Le code du lot doit être une chaîne',
                'lots.*.code.max'      => 'Le code du lot ne peut dépasser 50 caractères',

                // Intitulé lot
                'lots.*.intitule.required' => 'L’intitulé du lot est obligatoire',
                'lots.*.intitule.string'   => 'L’intitulé doit être une chaîne',
                'lots.*.intitule.max'      => 'L’intitulé ne peut dépasser 255 caractères',

                // Composants
                'lots.*.composants.required' => 'Chaque lot doit avoir au moins un composant',
                'lots.*.composants.array'    => 'Le format des composants est invalide',
                'lots.*.composants.min'      => 'Ajoutez au moins un composant',

                // Désignation
                'lots.*.composants.*.designation.required' => 'La désignation est obligatoire',
                'lots.*.composants.*.designation.string'   => 'La désignation doit être une chaîne',
                'lots.*.composants.*.designation.max'      => 'La désignation ne peut dépasser 255 caractères',

                // Unité
                'lots.*.composants.*.unite_id.required' => 'Veuillez sélectionner une unité',
                'lots.*.composants.*.unite_id.exists'   => 'Unité invalide',

                // Quantité
                'lots.*.composants.*.quantite.required' => 'La quantité est obligatoire',
                'lots.*.composants.*.quantite.numeric'  => 'La quantité doit être un nombre',
                'lots.*.composants.*.quantite.gt'       => 'La quantité doit être supérieure à 0',

                // Prix unitaire
                'lots.*.composants.*.prix_unitaire.required' => 'Le prix unitaire est obligatoire',
                'lots.*.composants.*.prix_unitaire.numeric'  => 'Le prix unitaire doit être un nombre',
                'lots.*.composants.*.prix_unitaire.min'      => 'Le prix unitaire ne peut pas être négatif',
            ]
        );


        if (collect($data['lots'])->isEmpty()) {


            return back()->with([
                'error' => 'Vous devez ajouter au moins un lot',
            ]);
        }

        foreach ($data['lots'] as $index => $lot) {
            if (empty($lot['composants'])) {
                return back()->with([
                    "error" => 'Chaque lot doit contenir au moins un composant',
                ]);
            }
        }



        DB::transaction(function () use ($data, $devis) {

            /** =========================
             * 1️ Supprimer anciens lots du corps d’état */

            $oldLots = DevisLot::where('devis_id', $devis->id)
                ->where('corps_etat_id', $data['corps_etat_id'])
                ->get();

            foreach ($oldLots as $lot) {
                $lot->composants()->delete();
                $lot->delete();
            }

            /** =========================
             * 2️ Recréer lots & composants  ========================= */
            foreach ($data['lots'] as $lotIndex => $lotData) {

                $lot = DevisLot::create([
                    'devis_id' => $devis->id,
                    'corps_etat_id' => $data['corps_etat_id'],
                    'code' => $lotData['code'],
                    'intitule' => $lotData['intitule'],
                    'ordre' => $lotIndex + 1,
                    'sous_total' => 0,
                ]);

                $sousTotalLot = 0;

                foreach ($lotData['composants'] as $compIndex => $comp) {
                    $montant = $comp['quantite'] * $comp['prix_unitaire'];

                    LotComposant::create([
                        'lot_id' => $lot->id,
                        'code' => "{$lot->code}." . ($compIndex + 1),
                        'designation' => $comp['designation'],
                        'unite_id' => $comp['unite_id'],
                        'quantite' => $comp['quantite'],
                        'prix_unitaire' => $comp['prix_unitaire'],
                        'montant' => $montant,
                    ]);

                    $sousTotalLot += $montant;
                }

                $lot->update(['sous_total' => $sousTotalLot]);
            }
        });

        return back()->with('success', 'Corps d’état enregistré avec succès.');
    }



    public function destroy(Batiment $batiment)
    {
        //  Sécurité d’accès 
        $this->validateBatimentAccess(
            $batiment,
            'ORG_DEVIS_QUANTITATIF_DELETE'
        );

        $devis = $batiment->devisEstimatifQuantitatif;

        if (!$devis) {
            return redirect()
                ->route('batiments.show', $batiment)
                ->with('error', 'Aucun devis à supprimer.');
        }

        //  Interdire la suppression si validé
        if ($devis->statut === 'valide') {
            return back()->with([
                'error' => 'Un devis validé ne peut pas être supprimé.',
            ]);
        }

        DB::transaction(function () use ($devis) {
            //  Suppression en cascade maîtrisée
            foreach ($devis->lots as $lot) {
                $lot->composants()->delete();
            }

            $devis->lots()->delete();
            $devis->delete();
        });

        return redirect()
            ->route('batiments.show', $batiment)
            ->with('success', 'Le devis a été supprimé avec succès.');
    }



    /**
     * Validation
     */
    public function valider(Batiment $batiment,  $devis)

    {
        $this->validateBatimentAccess(
            $batiment,
            'ORG_DEVIS_QUANTITATIF_VALIDE'
        );

        $devis = DevisEstimatifQuantitatif::find($devis);

        $this->assertBatimentDevis($batiment, $devis);

        $devis->update(['statut' => 'valide']);

        return back()->with('success', 'Devis validé.');
    }

    /**
     * Retour en brouillon
     */
    public function brouillon(Batiment $batiment,  $devis)
    {

        // $this->validateBatimentAccess(
        //     $batiment,
        //     'ORG_DEVIS_QUANTITATIF_NOVALIDE'
        // );
        $devis = DevisEstimatifQuantitatif::find($devis);


        $this->assertBatimentDevis($batiment, $devis);

        $devis->update(['statut' => 'brouillon']);

        return back()->with('success', 'Devis repassé en brouillon.');
    }

    /**
     * Vérification cohérence bâtiment / devisdevis-quantitatif
     */
    private function assertBatimentDevis(Batiment $batiment,  $devis): void
    {
        if ($devis->batiment_id !== $batiment->id) {
            abort(403, 'Accès non autorisé.');
        }
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
