<?php

namespace App\Http\Controllers;

use App\Models\DevisEstimatifQuantitatif;
use App\Models\CorpsEtat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TemplateEstimatifQte extends Controller
{
    /**
     * Liste tous les templates de devis estimatifs (is_template = true)
     */
    public function index()
    {
        $user = Auth::user();

        // Contrôle VIEW
        if (!$user->can('SYSTEM_TEMPLATE_DEVIS_ESTIMATIF_QUANTITATIF_VIEW') && !$user->can('ORG_DEVIS_QUANTITATIF_VIEW')) {
            abort(Response::HTTP_FORBIDDEN, "Vous n'avez pas la permission de voir les templates de devis estimatifs quantitatifs.");
        }

        $templates = DevisEstimatifQuantitatif::where('is_template', true)
            ->with([
                'batiment.projet.organisation',
                'lots.composants.unite',
                'lots.corpsEtat',
            ])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($template) use ($user) {
                // Calculer le nombre total de composants
                $nombreComposants = $template->lots->sum(function ($lot) {
                    return $lot->composants->count();
                });

                // Calculer le montant total
                $total = $template->lots->sum('sous_total');

                $batiment = $template->batiment;
                $projet = $batiment->projet ?? null;
                $organisation = $projet->organisation ?? null;

                // Permissions spécifiques à ce template
                $permissions = [
                    'canView' => $user->can('SYSTEM_TEMPLATE_DEVIS_ESTIMATIF_QUANTITATIF_VIEW') || $user->can('ORG_DEVIS_QUANTITATIF_VIEW'),
                ];

                return [
                    'id' => $template->id,
                    'code' => $template->code,
                    'intitule' => $template->intitule,
                    'statut' => $template->statut,
                    'created_at' => $template->created_at->format('d/m/Y'),
                    'nombre_composants' => $nombreComposants,
                    'total' => $total,
                    'batiment' => $batiment ? [
                        'id' => $batiment->id,
                        'nom' => $batiment->nom,
                        'code' => $batiment->code ?? '',
                    ] : null,
                    'projet' => $projet ? [
                        'id' => $projet->id,
                        'nom' => $projet->nom,
                        'code' => $projet->code ?? '',
                    ] : null,
                    'organisation' => $organisation ? [
                        'id' => $organisation->id,
                        'nom' => $organisation->nom,
                        'is_system' => $organisation->is_system ?? false,
                    ] : null,
                    'permissions' => $permissions,
                ];
            });

        return Inertia::render('Templates/DevisEstimatifQte/Index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Afficher un template spécifique
     */
    public function show(DevisEstimatifQuantitatif $template)
    {
        try {
            $user = Auth::user();

            if (!$template->is_template) {
                return back()->with('error', 'Ce devis n\'est pas un template.');
            }

            /**
             * VIEW : SYSTEM ou ORG
             */
            if (
                !$user->can('SYSTEM_TEMPLATE_DEVIS_ESTIMATIF_QUANTITATIF_VIEW') &&
                !$user->can('ORG_DEVIS_QUANTITATIF_VIEW')
            ) {
                abort(Response::HTTP_FORBIDDEN, "Vous n'avez pas la permission de voir ce template.");
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
            $devise = $template->batiment->projet->devise['libelle'] ?? 'MAD';

            $isSystemAdmin = $user->hasRole('SYSTEM_ADMIN_PLATEFORME');

            $permissions = [
                'canEdit' => $isSystemAdmin && $template->statut !== 'valide',
                'canDelete' => $isSystemAdmin && $template->statut !== 'valide',
                'canValidate' => $isSystemAdmin && $template->statut !== 'valide',
                'canCreate' => $user->can('ORG_DEVIS_QUANTITATIF_CREATE'),
            ];

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
                'devise' => $devise,
                'permissions' => $permissions,
            ]);

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
