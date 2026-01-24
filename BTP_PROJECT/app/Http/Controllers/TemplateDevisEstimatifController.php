<?php

namespace App\Http\Controllers;

use App\Models\DevisEstimatif;
use App\Models\NiveauBatiment;
use App\Models\UniteMesure;
use App\Models\Batiment;
use App\Models\Projet;
use App\Models\ComposantNiveau;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TemplateDevisEstimatifController extends Controller
{
    /**
     * Liste des templates de devis estimatifs
     */
    public function index()
    {
        $user = Auth::user();

        // Contrôle VIEW
        if (!$user->can('SYSTEM_TEMPLATE_DEVIS_ESTIMATIF_VIEW') && !$user->can('ORG_TEMPLATE_DEVIS_ESTIMATIF_VIEW')) {
            abort(Response::HTTP_FORBIDDEN, "Vous n'avez pas la permission de voir les templates de devis estimatifs.");
        }

        $templates = DevisEstimatif::with([
            'batiment.projet.organisation',
            'composants.niveau',
            'composants.unite',
        ])->where('is_template', true)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($devis) use ($user) {

                $batiment = $devis->batiment;
                $projet = $batiment->projet ?? null;
                $organisation = $projet->organisation ?? null;

                // Permissions spécifiques à ce template
                $permissions = [
                    'canView' => $user->can('SYSTEM_TEMPLATE_DEVIS_ESTIMATIF_VIEW') || $user->can('ORG_TEMPLATE_DEVIS_ESTIMATIF_VIEW'),

                ];

                return [
                    'id' => $devis->id,
                    'code' => $devis->code,
                    'intitule' => $devis->intitule,
                    'statut' => $devis->statut,
                    'created_at' => $devis->created_at->format('d/m/Y'),
                    'created_by' => $devis->created_by,
                    'total' => $devis->total(),
                    'nombre_composants' => $devis->composants->count(),
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

        return Inertia::render('Templates/DevisEstimatif/Index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Afficher les détails d'un template
     */
    public function show(DevisEstimatif $template)
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
                !$user->can('SYSTEM_TEMPLATE_DEVIS_ESTIMATIF_VIEW') &&
                !$user->can('ORG_DEVIS_ESTIMATIF_VIEW')
            ) {
                abort(Response::HTTP_FORBIDDEN, "Vous n'avez pas la permission de voir ce template.");
            }

            $template->load([
                'batiment.projet.organisation',
                'composants.niveau',
                'composants.unite',
            ]);

            $totauxParNiveau = $template->composants
                ->groupBy('niveau_id')
                ->map(fn($items, $niveauId) => [
                    'niveau_id' => (int) $niveauId,
                    'total' => $items->sum('montant'),
                ])
                ->values();

            $totalGeneral = $template->composants->sum('montant');
            $devise = $template->batiment->projet->devise->libelle ?? 'MAD';


            $isSystemAdmin = $user->hasRole('SYSTEM_ADMIN_PLATEFORME');

            $permissions = [
                'canEdit' => $isSystemAdmin && $template->statut !== 'valide',
                'canDelete' => $isSystemAdmin && $template->statut !== 'valide',
                'canValidate' => $isSystemAdmin && $template->statut !== 'valide',
                'canCreate' => $user->can('ORG_DEVIS_ESTIMATIF_CREATE'),
            ];

            return Inertia::render('Templates/DevisEstimatif/Show', [
                'template' => $template,
                'totauxParNiveau' => $totauxParNiveau,
                'totalGeneral' => $totalGeneral,
                'devise' => $devise,
                'permissions' => $permissions,
            ]);

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    /**
     * Réutiliser un template pour créer un nouveau devis
     */
    public function reuse(DevisEstimatif $template)
    {
        $user = Auth::user();

        if (!$template->is_template) {
            abort(404, 'Ce devis n\'est pas un template.');
        }

        if (!$user->can('SYSTEM_TEMPLATE_DEVIS_ESTIMATIF_VIEW') && !$user->can('ORG_DEVIS_ESTIMATIF_VIEW')) {
            abort(Response::HTTP_FORBIDDEN, "Vous n'avez pas la permission de voir ce template.");
        }

        $template->load([
            'batiment.projet.organisation',
            'composants.niveau',
            'composants.unite',
        ]);

        $activeOrganisationId = getPermissionsTeamId();
        $projets = Projet::where('organisation_id', $activeOrganisationId)->orderBy('nom')->get();
        $niveaux = NiveauBatiment::orderBy('code')->get();
        $unites = UniteMesure::orderBy('libelle')->get();

        $composantsSource = $template->composants
            ->groupBy('niveau_id')
            ->map(fn($items, $niveauId) => [
                'niveau_id' => (int) $niveauId,
                'composants' => $items->map(fn($c) => [
                    'id' => $c->id,
                    'code' => $c->code,
                    'piece' => $c->piece,
                    'unite_id' => $c->unite_id,
                    'prix_unitaire' => $c->prix_unitaire,
                    'qte' => $c->qte,
                ])->values()->all(),
            ])->values()->all();

        return Inertia::render('Templates/DevisEstimatif/Reuse', [
            'template' => [
                'id' => $template->id,
                'code' => $template->code,
                'intitule' => $template->intitule,
            ],
            'batimentSource' => [
                'id' => $template->batiment->id,
                'nom' => $template->batiment->nom,
            ],
            'projetSource' => [
                'id' => $template->batiment->projet->id,
                'nom' => $template->batiment->projet->nom,
            ],
            'composantsSource' => $composantsSource,
            'projets' => $projets,
            'niveaux' => $niveaux,
            'unites' => $unites,
            'permissions' => [
                'canCreate' => $user->can('ORG_DEVIS_ESTIMATIF_CREATE'),
            ],
        ]);
    }

    /**
     * Créer un devis à partir d'un template
     */
    public function storeReuse(Request $request, DevisEstimatif $template)
    {
        $user = Auth::user();

        if (!$template->is_template) {
            abort(404, 'Ce devis n\'est pas un template.');
        }

        if (!$user->can('ORG_DEVIS_ESTIMATIF_CREATE')) {
            abort(Response::HTTP_FORBIDDEN, "Vous n'avez pas la permission de créer des devis estimatifs.");
        }

        $request->validate([
            'batiment_id' => ['required', 'exists:batiments,id'],
            'intitule' => 'required|string|max:255',
            'niveaux' => 'required|array|min:1',
        ]);

        $batiment = Batiment::findOrFail($request->batiment_id);
        $activeOrganisationId = getPermissionsTeamId();
        if ($batiment->projet->organisation_id !== $activeOrganisationId) {
            abort(403, 'Accès refusé à ce bâtiment.');
        }

        return DB::transaction(function () use ($request, $batiment, $template, $user) {
            $newDevis = DevisEstimatif::create([
                'intitule' => $request->intitule,
                'batiment_id' => $request->batiment_id,
                'code' => $this->generateCode(),
                'statut' => 'brouillon',
                'is_template' => true,
                'created_by' => $user->id,
            ]);

            foreach ($request->niveaux as $niveau) {
                foreach ($niveau['composants'] as $comp) {
                    ComposantNiveau::create([
                        'devis_estimatif_id' => $newDevis->id,
                        'niveau_id' => $niveau['niveau_id'],
                        'code' => $comp['code'],
                        'piece' => $comp['piece'],
                        'unite_id' => $comp['unite_id'],
                        'qte' => $comp['qte'],
                        'prix_unitaire' => $comp['prix_unitaire'],
                        'montant' => bcmul($comp['qte'], $comp['prix_unitaire'], 2),
                    ]);
                }
            }

            Log::info('Devis créé à partir d\'un template', [
                'template_id' => $template->id,
                'nouveau_devis_id' => $newDevis->id,
                'user_id' => $user->id,
            ]);

            return redirect()->route('batiments.devisestimatif.show', [
                'batiment' => $batiment->id,
                'devisestimatif' => $newDevis->id,
            ])->with('success', "Devis créé avec succès à partir du template {$template->code}.");
        });
    }

    /**
     * Générer le code d'un devis
     */
    private function generateCode(): string
    {
        $lastId = DevisEstimatif::max('id') ?? 0;
        return 'DEV-' . date('Y') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
    }


    public function validateTemplate(DevisEstimatif $template)
    {
        $user = Auth::user();

        if (!$user->can('SYSTEM_TEMPLATE_DEVIS_ESTIMATIF_VALIDATE')) {
            abort(403, "Action non autorisée.");
        }

        if (!$template->is_template) {
            abort(400, "Ce devis n'est pas un template.");
        }

        $template->update([
            'statut' => 'valide',
        ]);

        return back()->with('success', 'Template validé avec succès.');
    }

    public function unvalidateTemplate(DevisEstimatif $template)
    {
        $user = Auth::user();

        if (!$user->can('SYSTEM_TEMPLATE_DEVIS_ESTIMATIF_NOT_VALIDATE')) {
            abort(403, "Action non autorisée.");
        }

        if (!$template->is_template) {
            abort(400, "Ce devis n'est pas un template.");
        }

        $template->update([
            'statut' => 'brouillon',
        ]);

        return back()->with('success', 'Template remis en brouillon.');
    }

}
