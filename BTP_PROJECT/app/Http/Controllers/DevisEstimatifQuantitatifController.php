<?php

namespace App\Http\Controllers;

use App\Models\Batiment;
use App\Models\CorpsEtat;
use App\Models\DevisEstimatifQuantitatif;
use App\Models\DevisLot;
use App\Models\LotComposant;
use App\Models\Projet;
use App\Models\UniteMesure;
use App\Services\OrganisationContext;
use Barryvdh\DomPDF\Facade\Pdf;
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
                'id' => $batiment->id,
                'nom' => $batiment->nom,
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
            'is_template' => $batiment->projet->organisation['is_system'] ? true : false,
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
        try {
            // Sécurité d'accès
            $this->validateBatimentAccess(
                $batiment,
                'ORG_DEVIS_QUANTITATIF_EDIT'
            );

            // Le bâtiment n'a qu'un seul devis
            $devis = $batiment->devisEstimatifQuantitatif()->first();

            // Vérifier si le devis existe
            if (!$devis) {
                return redirect()
                    ->route('batiments.show', $batiment)
                    ->with('error', 'Aucun devis trouvé pour ce bâtiment.');
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
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'édition du devis', [
                'message' => $e->getMessage(),
                'batiment_id' => $batiment->id,
            ]);

            return redirect()
                ->route('batiments.show', $batiment)
                ->with('error', 'Une erreur est survenue lors du chargement du devis.');
        }
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
        //  Sécurité d'accès (optionnel)
        $this->validateBatimentAccess(
            $batiment,
            'ORG_DEVIS_QUANTITATIF_VIEW'
        );

        $user = Auth::user();

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
         * Tous les corps d'état,
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

        $isSystemAdmin = $user->hasRole('SYSTEM_ADMIN_PLATEFORME');
        $isAuthor = $devis->created_by === $user->id;
        $canModify = ($isSystemAdmin || $isAuthor) && $devis->statut !== 'valide';

        $permissions = [
            'canEdit' => $canModify,
            'canDelete' => $canModify,
            'canValidate' => $canModify,
            'canCreate' => $user->can('ORG_DEVIS_QUANTITATIF_CREATE'),
        ];

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
            'permissions' => $permissions,
        ]);
    }
    /**
     * Edition
     */
    public function editCorpsEtat(Batiment $batiment, $devis)
    {
        try {
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

            // Vérifier que le devis appartient bien au bâtiment
            if ($devis->batiment_id !== $batiment->id) {
                return redirect()
                    ->route('batiments.show', $batiment)
                    ->with('error', 'Ce devis n\'appartient pas à ce bâtiment.');
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
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'édition du devis', [
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('batiments.show', $batiment)
                ->with('error', 'Une erreur est survenue lors du chargement du devis.');
        }
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
                'corps_etat_id.exists' => 'Corps d’état invalide',

                // Lots
                'lots.required' => 'Au moins un lot est requis',
                'lots.array' => 'Le format des lots est invalide',
                'lots.min' => 'Vous devez ajouter au moins un lot',

                // Code lot
                'lots.*.code.required' => 'Le code du lot est obligatoire',
                'lots.*.code.string' => 'Le code du lot doit être une chaîne',
                'lots.*.code.max' => 'Le code du lot ne peut dépasser 50 caractères',

                // Intitulé lot
                'lots.*.intitule.required' => 'L’intitulé du lot est obligatoire',
                'lots.*.intitule.string' => 'L’intitulé doit être une chaîne',
                'lots.*.intitule.max' => 'L’intitulé ne peut dépasser 255 caractères',

                // Composants
                'lots.*.composants.required' => 'Chaque lot doit avoir au moins un composant',
                'lots.*.composants.array' => 'Le format des composants est invalide',
                'lots.*.composants.min' => 'Ajoutez au moins un composant',

                // Désignation
                'lots.*.composants.*.designation.required' => 'La désignation est obligatoire',
                'lots.*.composants.*.designation.string' => 'La désignation doit être une chaîne',
                'lots.*.composants.*.designation.max' => 'La désignation ne peut dépasser 255 caractères',

                // Unité
                'lots.*.composants.*.unite_id.required' => 'Veuillez sélectionner une unité',
                'lots.*.composants.*.unite_id.exists' => 'Unité invalide',

                // Quantité
                'lots.*.composants.*.quantite.required' => 'La quantité est obligatoire',
                'lots.*.composants.*.quantite.numeric' => 'La quantité doit être un nombre',
                'lots.*.composants.*.quantite.gt' => 'La quantité doit être supérieure à 0',

                // Prix unitaire
                'lots.*.composants.*.prix_unitaire.required' => 'Le prix unitaire est obligatoire',
                'lots.*.composants.*.prix_unitaire.numeric' => 'Le prix unitaire doit être un nombre',
                'lots.*.composants.*.prix_unitaire.min' => 'Le prix unitaire ne peut pas être négatif',
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
    public function valider(Batiment $batiment, $devis)
    {
        try {
            $this->validateBatimentAccess(
                $batiment,
                'ORG_DEVIS_QUANTITATIF_VALIDE'
            );

            $devis = DevisEstimatifQuantitatif::find($devis);

            // Vérifier si le devis existe
            if (!$devis) {
                return back()->with('error', 'Devis introuvable.');
            }

            // Vérifier que le devis appartient au bâtiment
            $this->assertBatimentDevis($batiment, $devis);

            // Vérifier que le devis n'est pas déjà validé
            if ($devis->statut === 'valide') {
                return back()->with('error', 'Ce devis est déjà validé.');
            }

            // Vérifier que le devis a au moins un lot
            if ($devis->lots()->count() === 0) {
                return back()->with('error', 'Impossible de valider un devis vide. Ajoutez au moins un lot.');
            }

            $devis->update([
                'statut' => 'valide',
                'updated_by' => Auth::id(),
            ]);

            return back()->with('success', 'Devis validé avec succès.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return back()->with('error', 'Vous n\'avez pas la permission de valider ce devis.');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la validation du devis', [
                'message' => $e->getMessage(),
                'batiment_id' => $batiment->id,
                'devis_id' => $devis,
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'La validation du devis a échoué. Veuillez vérifier les données saisies et réessayer. Si l\'erreur persiste, contactez l\'administrateur.');
        }
    }

    /**
     * Retour en brouillon
     */
    public function brouillon(Batiment $batiment, $devis)
    {

        $this->validateBatimentAccess(
            $batiment,
            'ORG_DEVIS_QUANTITATIF_NOVALIDE'
        );
        $devis = DevisEstimatifQuantitatif::find($devis);


        $this->assertBatimentDevis($batiment, $devis);

        $devis->update(['statut' => 'brouillon']);

        return back()->with('success', 'Devis repassé en brouillon.');
    }


    /**
     * Formulaire de sélection du bâtiment cible pour la réutilisation
     */
    public function reuseForm(Batiment $batiment)
    {
        $devis = $batiment->devisEstimatifQuantitatif()->firstOrFail();

        $activeOrganisationId = getPermissionsTeamId();

        $projets = Projet::where('organisation_id', $activeOrganisationId)
            ->with([
                'batiments' => function ($query) {
                    $query->whereDoesntHave('devisEstimatifQuantitatif');
                }
            ])
            ->get()
            ->filter(fn($projet) => $projet->batiments->isNotEmpty())
            ->map(function ($projet) {
                return [
                    'id' => $projet->id,
                    'nom' => $projet->nom,
                    'code' => $projet->code,
                    'batiments' => $projet->batiments->map(fn($bat) => [
                        'id' => $bat->id,
                        'nom' => $bat->nom,
                        'code' => $bat->code,
                    ]),
                ];
            });

        return Inertia::render('Organisations/DevisEstimatifQte/Reuse', [
            'batimentSource' => [
                'id' => $batiment->id,
                'nom' => $batiment->nom,
                'code' => $batiment->code,
            ],
            'devisSource' => [
                'id' => $devis->id,
                'intitule' => $devis->intitule,
                'code' => $devis->code,
                'statut' => $devis->statut,
            ],
            'projets' => $projets->values(),
        ]);
    }

    /**
     * Réutilisation du devis sur un autre bâtiment
     *
     */
    public function reuse(Request $request, Batiment $batiment, DevisEstimatifQuantitatif $devis)
    {
        // Vérifier que le devis appartient bien au bâtiment source
        if ($devis->batiment_id !== $batiment->id) {
            return back()->with('error', 'Le devis ne correspond pas au bâtiment source.');
        }

        $data = $request->validate([
            'batiment_cible_id' => 'required|exists:batiments,id',
            'nouveau_code' => 'required|string|max:50|unique:devis_estimatif_quantitatif,code',
            'nouvel_intitule' => 'nullable|string|max:255',
        ]);

        $batimentCible = Batiment::findOrFail($data['batiment_cible_id']);

        // Vérifier que le bâtiment cible n'a pas déjà de devis
        if ($batimentCible->devisEstimatifQuantitatif()->exists()) {
            return back()->with('error', 'Le bâtiment cible possède déjà un devis.');
        }

        // Charger le devis source avec lots et composants
        $devis->load('lots.composants');

        DB::beginTransaction();

        try {
            // Créer le nouveau devis
            $nouveauDevis = DevisEstimatifQuantitatif::create([
                'batiment_id' => $batimentCible->id,
                'intitule' => $data['nouvel_intitule'] ?? $devis->intitule,
                'code' => $data['nouveau_code'],
                'statut' => 'brouillon',
                'is_template' => false,
                'created_by' => Auth::id(),
            ]);

            // Dupliquer les lots et composants
            foreach ($devis->lots as $lotSource) {
                $nouveauLot = DevisLot::create([
                    'devis_id' => $nouveauDevis->id,
                    'corps_etat_id' => $lotSource->corps_etat_id,
                    'code' => $lotSource->code,
                    'intitule' => $lotSource->intitule,
                    'ordre' => $lotSource->ordre,
                    'sous_total' => $lotSource->sous_total,
                ]);

                foreach ($lotSource->composants as $composantSource) {
                    LotComposant::create([
                        'lot_id' => $nouveauLot->id,
                        'code' => $composantSource->code,
                        'designation' => $composantSource->designation,
                        'unite_id' => $composantSource->unite_id,
                        'quantite' => $composantSource->quantite,
                        'prix_unitaire' => $composantSource->prix_unitaire,
                        'montant' => $composantSource->montant,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route(
                'batiments.devis-estimatif-quantitatif.show',
                [
                    'batiment' => $batimentCible->id,
                    'devis_estimatif_quantitatif' => $nouveauDevis->id,
                ]
            )->with('success', 'Devis réutilisé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Erreur réutilisation devis', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'batiment_source' => $batiment->nom,
                'batiment_cible' => $batimentCible->nom,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la réutilisation : ' . $e->getMessage());
        }
    }

    /**
     * Vérification cohérence bâtiment / devisdevis-quantitatif
     */
    private function assertBatimentDevis(Batiment $batiment, $devis): void
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


    /**
     * Télécharger le devis quantitatif en PDF
     */
    /**
     * Télécharger le devis quantitatif en PDF
     */
    public function downloadPdf($batiment, $devis_estimatif_quantitatif)
    {
        // Convertir en objets si ce sont des IDs
        $batiment = Batiment::find($batiment);
        $devis = DevisEstimatifQuantitatif::find($devis_estimatif_quantitatif);

        if (!$batiment || !$devis) {
            abort(404, 'Ressource non trouvée.');
        }

        // Vérifier que le devis appartient bien au bâtiment
        if ($devis->batiment_id !== $batiment->id) {
            abort(404, 'Ce devis n\'appartient pas à ce bâtiment.');
        }

        // Sécurité d’accès
        $this->validateBatimentAccess(
            $batiment,
            'ORG_DEVIS_QUANTITATIF_VIEW'
        );

        // Charger toutes les relations nécessaires
        $devis->load([
            'batiment.projet.organisation',
            'lots.composants.unite',
            'lots.corpsEtat',
        ]);

        /**
         * Préparer les corps d’état avec leurs lots
         */
        $corpsEtats = CorpsEtat::orderBy('ordre')
            ->get()
            ->map(function ($ce) use ($devis) {

                $lots = $devis->lots
                    ->where('corps_etat_id', $ce->id)
                    ->values()
                    ->map(function ($lot) {
                        return [
                            'code' => $lot->code,
                            'intitule' => $lot->intitule,
                            'sous_total' => $lot->sous_total,
                            'composants' => $lot->composants->map(fn($c) => [
                                'code' => $c->code,
                                'designation' => $c->designation,
                                'quantite' => $c->quantite,
                                'prix_unitaire' => $c->prix_unitaire,
                                'montant' => $c->montant,
                                'unite' => [
                                    'code' => $c->unite->code ?? null,
                                ],
                            ]),
                        ];
                    });

                return [
                    'id' => $ce->id,
                    'intitule' => $ce->intitule,
                    'lots' => $lots,
                    'total' => $lots->sum('sous_total'),
                ];
            })
            ->filter(fn($ce) => $ce['lots']->isNotEmpty());

        // Total général
        $totalGeneral = $corpsEtats->sum('total');

        $devise = $devis->batiment->projet->devise['libelle'] ?? 'Franc CFA';

        // Vérifiez que la vue existe
        $viewPath = 'DevisEstimQte/pdf';

        // Génération du PDF
        $pdf = Pdf::loadView($viewPath, [
            'devis' => $devis,
            'batiment' => $devis->batiment,
            'projet' => $devis->batiment->projet,
            'organisation' => $devis->batiment->projet->organisation,
            'corpsEtats' => $corpsEtats,
            'totalGeneral' => $totalGeneral,
            'devise' => $devise,
        ]);

        return $pdf->stream("devis-quantitatif-{$devis->code}.pdf");
    }



}
