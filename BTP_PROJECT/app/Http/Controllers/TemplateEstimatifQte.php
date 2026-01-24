<?php

namespace App\Http\Controllers;

use App\Models\DevisEstimatifQuantitatif;
use App\Models\CorpsEtat;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TemplateEstimatifQte extends Controller
{
    /**
     * Liste tous les templates de devis estimatifs (is_template = true)
     */
    public function index()
    {
        $templates = DevisEstimatifQuantitatif::where('is_template', true)
            ->with([
                'batiment.projet.organisation',
                'lots.composants.unite',
                'lots.corpsEtat',
            ])
            ->get()
            ->map(function ($template) {
                // Calculer le nombre total de composants
                $nombreComposants = $template->lots->sum(function ($lot) {
                    return $lot->composants->count();
                });

                // Calculer le montant total
                $total = $template->lots->sum('sous_total');

                return [
                    'id' => $template->id,
                    'code' => $template->code,
                    'intitule' => $template->intitule,
                    'statut' => $template->statut,
                    'created_at' => $template->created_at->format('d/m/Y'),
                    'nombre_composants' => $nombreComposants,
                    'total' => $total,
                    'batiment' => [
                        'id' => $template->batiment->id,
                        'nom' => $template->batiment->nom,
                        'code' => $template->batiment->code,
                    ],
                    'projet' => [
                        'id' => $template->batiment->projet->id,
                        'nom' => $template->batiment->projet->nom,
                        'code' => $template->batiment->projet->code,
                    ],
                    'organisation' => [
                        'id' => $template->batiment->projet->organisation->id,
                        'nom' => $template->batiment->projet->organisation->nom,
                        'is_system' => $template->batiment->projet->organisation->is_system ?? false,
                    ],
                ];
            });
        return Inertia::render('Templates/DevisEstimatifQte/Index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Afficher un template spécifique
     * Redirige vers le show du DevisEstimatifQuantitatifController
     */
    public function show(DevisEstimatifQuantitatif $template)
    {
        // Vérifier que c'est bien un template
        if (!$template->is_template) {
            return redirect()
                ->route('templates-estimatif-qte.index')
                ->with('error', 'Ce devis n\'est pas un template.');
        }

        // Charger les relations nécessaires
        $template->load([
            'batiment.projet.organisation',
            'lots.composants.unite',
            'lots.corpsEtat',
        ]);

        /**
         * Tous les corps d'état, même ceux sans lots
         */
        $corpsEtats = CorpsEtat::orderBy('ordre')->get()->map(function ($ce) use ($template) {
            $lots = $template->lots
                ->where('corps_etat_id', $ce->id)
                ->values()
                ->map(fn($lot) => [
                    'code' => $lot->code,
                    'intitule' => $lot->intitule,
                    'sous_total' => $lot->sous_total,
                    'composants' => $lot->composants->map(fn($c) => [
                        'designation' => $c->designation,
                        'quantite' => $c->quantite,
                        'prix_unitaire' => $c->prix_unitaire,
                        'montant' => $c->montant,
                        'unite' => [
                            'code' => $c->unite->code ?? null,
                            'libelle' => $c->unite->libelle ?? null,
                        ],
                    ]),
                ]);

            return [
                'id' => $ce->id,
                'intitule' => $ce->intitule,
                'lots' => $lots,
            ];
        });

        // Calculer le total général
        $totalGeneral = $template->lots->sum('sous_total');

        return Inertia::render('Organisations/DevisEstimatifQte/Show', [
            'batiment' => [
                'id' => $template->batiment->id,
                'nom' => $template->batiment->nom,
                'code' => $template->batiment->code,
            ],
            'devis' => [
                'id' => $template->id,
                'intitule' => $template->intitule,
                'code' => $template->code,
                'statut' => $template->statut,
                'is_template' => true,
            ],
            'template' => [
                'id' => $template->id,
                'code' => $template->code,
                'intitule' => $template->intitule,
                'statut' => $template->statut,
                'created_at' => $template->created_at->format('d/m/Y H:i'),
                'batiment' => [
                    'id' => $template->batiment->id,
                    'nom' => $template->batiment->nom,
                    'code' => $template->batiment->code,
                    'projet' => [
                        'nom' => $template->batiment->projet->nom,
                        'organisation' => [
                            'nom' => $template->batiment->projet->organisation->nom,
                            'is_system' => $template->batiment->projet->organisation->is_system ?? false,
                        ],
                    ],
                ],
            ],
            'corpsEtats' => $corpsEtats,
            'totalGeneral' => $totalGeneral,
            'devise' => $template->batiment->projet->devise['libelle'] ?? 'MAD',
        ]);
    }
}