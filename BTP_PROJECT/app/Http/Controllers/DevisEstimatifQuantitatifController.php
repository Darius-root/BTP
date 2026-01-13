<?php

namespace App\Http\Controllers;

use App\Models\Batiment;
use App\Models\CorpsEtat;
use App\Models\DevisEstimatifQuantitatif;
use App\Models\DevisLot;
use App\Models\LotComposant;
use App\Models\UniteMesure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DevisEstimatifQuantitatifController extends Controller
{
    /**
     * Formulaire de création
     */
    public function create(Batiment $batiment)
    {
        //  Sécurité métier
        // $this->validateBatimentAccess($batiment, 'DEVIS_QUANTITATIF_CREATE');

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
        // $this->validateBatimentAccess(
        //     $batiment,
        //     'ORG_DEVIS_QUANTITATIF_CREATE'
        // );

        if ($batiment->devisEstimatifQuantitatif()->exists()) {
            return redirect()->route('batiments.devis-estimatif-quantitatif.edit', ['batiment' => $batiment->id, 'devis_estimatif_quantitatif' => $batiment->devisEstimatifQuantitatif->id,])->with('error', 'Ce bâtiment possède déjà un devis. Vous avez été redirigé vers son édition.');
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
            ->route('batiments.devis-estimatif-quantitatif.edit', [
                'batiment' => $batiment->id,
                'devis_estimatif_quantitatif' => $devis->id,
            ])
            ->with('success', 'Devis créé. Vous pouvez maintenant saisir les quantités.');
    }


    /**
     * Affichage
     */
    public function show(Batiment $batiment, DevisEstimatifQuantitatif $devis)
    {
        $this->assertBatimentDevis($batiment, $devis);

        $devis->load([
            'lots.corpsEtat',
            'lots.composants.unite',
        ]);

        return Inertia::render('Organisations/DevisEstimatifQte/Show', [
            'batiment' => $batiment,
            'devis' => $devis,
            'totalGeneral' => $devis->total(),
        ]);
    }

    /**
     * Edition
     */
    public function edit(Batiment $batiment, DevisEstimatifQuantitatif $devis)
    {
        // $this->validateBatimentAccess(
        //     $batiment,
        //     'ORG_DEVIS_QUANTITATIF_EDIT'
        // );

        $devis->load([
            'lots.composants.unite',
            'lots.corpsEtat',
        ]);


        return Inertia::render('Organisations/DevisEstimatifQte/Edit', [
            'batiment' => [
                'id' => $batiment->id,
                'nom' => $batiment->nom,
            ],
            'devis' => [
                'id' => $devis->id,
                'intitule' => $devis->intitule,
                'statut' => $devis->statut,
            ],
            'corpsEtats' => CorpsEtat::orderBy('ordre')->get(),
            'unites' => UniteMesure::orderBy('libelle')->get(),
            'lots' => $devis->lots,
        ]);
    }


    public function update(Request $request, DevisEstimatifQuantitatif $devis)
    {

    dd($request->all());
        $data = $request->validate([
            'corps_etats' => ['required', 'array'],

            'corps_etats.*.corps_etat_id' => ['required', 'exists:corps_etat,id'],
            'corps_etats.*.lots' => ['nullable', 'array'],

            'corps_etats.*.lots.*.code' => ['required', 'string'],
            'corps_etats.*.lots.*.intitule' => ['required', 'string'],
            'corps_etats.*.lots.*.composants' => ['nullable', 'array'],

            'corps_etats.*.lots.*.composants.*.designation' => ['required', 'string'],
            'corps_etats.*.lots.*.composants.*.unite_id' => ['required', 'exists:unites_mesure,id'],
            'corps_etats.*.lots.*.composants.*.quantite' => ['required', 'numeric', 'min:0'],
            'corps_etats.*.lots.*.composants.*.prix_unitaire' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($data, $devis) {

            // Nettoyage (simple et fiable)
            $devis->lots()->delete();

            foreach ($data['corps_etats'] as $ceData) {

                $corpsEtatId = $ceData['corps_etat_id'];
                $sousTotalCorpsEtat = 0;

                foreach ($ceData['lots'] ?? [] as $lotData) {

                    $lot = DevisLot::create([
                        'devis_id'      => $devis->id,
                        'corps_etat_id' => $corpsEtatId,
                        'code'          => $lotData['code'],
                        'intitule'      => $lotData['intitule'],
                        'ordre'         => 0,
                        'sous_total'    => 0,
                    ]);

                    $sousTotalLot = 0;

                    foreach ($lotData['composants'] ?? [] as $compData) {

                        $montant = $compData['quantite'] * $compData['prix_unitaire'];

                        LotComposant::create([
                            'lot_id'        => $lot->id,
                            'code'          => null,
                            'designation'   => $compData['designation'],
                            'unite_id'      => $compData['unite_id'],
                            'quantite'      => $compData['quantite'],
                            'prix_unitaire' => $compData['prix_unitaire'],
                            'montant'       => $montant,
                        ]);

                        $sousTotalLot += $montant;
                    }

                    // Mise à jour sous-total du lot
                    $lot->update([
                        'sous_total' => $sousTotalLot,
                    ]);

                    $sousTotalCorpsEtat += $sousTotalLot;
                }

                // Mise à jour sous-total du corps d’état
                DB::table('corps_etat')
                    ->where('id', $corpsEtatId)
                    ->update([
                        'sous_total' => $sousTotalCorpsEtat,
                    ]);
            }
        });

        return redirect()
            ->route('devis-quantitatif.edit', $devis)
            ->with('success', 'Devis quantitatif enregistré avec succès.');
    }



    /**
     * Validation
     */
    public function valider(Batiment $batiment, DevisEstimatifQuantitatif $devis)
    {
        $this->assertBatimentDevis($batiment, $devis);

        $devis->update(['statut' => 'valide']);

        return back()->with('success', 'Devis validé.');
    }

    /**
     * Retour en brouillon
     */
    public function brouillon(Batiment $batiment, DevisEstimatifQuantitatif $devis)
    {
        $this->assertBatimentDevis($batiment, $devis);

        $devis->update(['statut' => 'brouillon']);

        return back()->with('success', 'Devis repassé en brouillon.');
    }

    /**
     * Vérification cohérence bâtiment / devis
     */
    private function assertBatimentDevis(Batiment $batiment, DevisEstimatifQuantitatif $devis): void
    {
        if ($devis->batiment_id !== $batiment->id) {
            abort(403, 'Accès non autorisé.');
        }
    }
}
