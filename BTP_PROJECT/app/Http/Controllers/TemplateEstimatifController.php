<?php

namespace App\Http\Controllers;

use App\Models\DevisEstimatif;
use App\Models\NiveauBatiment;
use App\Models\UniteMesure;
use App\Services\OrganisationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class TemplateEstimatifController extends Controller
{
    /**
     * Afficher la liste des devis templates (is_template = true)
     */
    public function index()
    {
        $this->validatePermission('ORG_DEVIS_ESTIMATIF_VIEW');

        $templates = DevisEstimatif::where('is_template', true)
            ->with([
                'batiment.projet.organisation',
                'composants.niveau',
                'composants.unite',
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($devis) {
                return [
                    'id' => $devis->id,
                    'code' => $devis->code,
                    'intitule' => $devis->intitule,
                    'statut' => $devis->statut,
                    'created_at' => $devis->created_at->format('d/m/Y'),
                    'created_by' => $devis->created_by,
                    'total' => $devis->total(),
                    'nombre_composants' => $devis->composants->count(),
                    'batiment' => [
                        'id' => $devis->batiment->id,
                        'nom' => $devis->batiment->nom,
                        'code' => $devis->batiment->code ?? '',
                    ],
                    'projet' => [
                        'id' => $devis->batiment->projet->id,
                        'nom' => $devis->batiment->projet->nom,
                        'code' => $devis->batiment->projet->code ?? '',
                    ],
                    'organisation' => [
                        'id' => $devis->batiment->projet->organisation->id,
                        'nom' => $devis->batiment->projet->organisation->nom,
                        'is_system' => $devis->batiment->projet->organisation->is_system ?? false,
                    ],
                ];
            });

        return Inertia::render('Templates/DevisEstimatif/Index', [
            'templates' => $templates,
            'auth' => [
                'user' => Auth::user(),
                'permissions' => Auth::user()?->getAllPermissions()->pluck('name')->toArray() ?? [],
            ],
        ]);
    }

    /**
     * Afficher les détails d'un template
     */
    public function show(DevisEstimatif $template)
    {
        if (!$template->is_template) {
            return redirect()
                ->route('templates.index')
                ->with('error', 'Ce devis n\'est pas un template.');
        }

        $this->validatePermission('ORG_DEVIS_ESTIMATIF_VIEW');

        $template->load([
            'batiment' => function ($query) {
                $query->with([
                    'projet' => function ($query) {
                        $query->with('organisation');
                    }
                ]);
            },
            'composants' => function ($query) {
                $query->with(['niveau', 'unite']);
            },
        ]);

        if (!$template->batiment || !$template->batiment->projet || !$template->batiment->projet->organisation) {
            return redirect()
                ->route('templates.index')
                ->with('error', 'Le template n\'est pas associé à une organisation valide.');
        }

        $devise = $template->batiment->projet->devise['libelle'] ?? 'MAD';

        return Inertia::render('Templates/DevisEstimatif/Show', [
            'template' => [
                'id' => $template->id,
                'code' => $template->code,
                'intitule' => $template->intitule,
                'statut' => $template->statut,
                'created_at' => $template->created_at->format('d/m/Y'),
                'created_by' => $template->created_by,

                'batiment' => [
                    'id' => $template->batiment->id,
                    'nom' => $template->batiment->nom,
                    'code' => $template->batiment->code ?? '',
                    'projet' => [
                        'id' => $template->batiment->projet->id,
                        'nom' => $template->batiment->projet->nom,
                        'code' => $template->batiment->projet->code ?? '',
                        'organisation' => [
                            'id' => $template->batiment->projet->organisation->id,
                            'nom' => $template->batiment->projet->organisation->nom,
                            'is_system' => $template->batiment->projet->organisation->is_system ?? false,
                        ],
                    ],
                ],

                'composants' => $template->composants->map(fn($c) => [
                    'id' => $c->id,
                    'code' => $c->code,
                    'piece' => $c->piece,
                    'qte' => $c->qte,
                    'prix_unitaire' => $c->prix_unitaire,
                    'montant' => $c->montant,
                    'niveau' => [
                        'id' => $c->niveau->id,
                        'nom' => $c->niveau->nom,
                        'code' => $c->niveau->code ?? '',
                    ],
                    'unite' => [
                        'id' => $c->unite->id,
                        'nom' => $c->unite->libelle,
                    ],
                ]),
            ],

            'totauxParNiveau' => $template->totalParNiveau()->map(fn($row) => [
                'niveau_id' => $row->niveau_id,
                'niveau_nom' => $row->niveau->nom,
                'total' => $row->total,
            ])->values(),

            'totalGeneral' => $template->total(),
            'devise' => $devise,
            'auth' => [
                'user' => Auth::user(),
                'permissions' => Auth::user()?->getAllPermissions()->pluck('name')->toArray() ?? [],
            ],
        ]);
    }


    /**
     * Rechercher des templates avec filtres
     */
    public function search(Request $request)
    {
        $this->validatePermission('ORG_DEVIS_ESTIMATIF_VIEW');

        $query = DevisEstimatif::where('is_template', true)
            ->with([
                'batiment.projet.organisation',
                'composants.niveau',
                'composants.unite',
            ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('intitule', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->boolean('system_only')) {
            $query->whereHas('batiment.projet.organisation', function ($q) {
                $q->where('is_system', true);
            });
        }

        $templates = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 15))
            ->through(function ($devis) {
                return [
                    'id' => $devis->id,
                    'code' => $devis->code,
                    'intitule' => $devis->intitule,
                    'statut' => $devis->statut,
                    'created_at' => $devis->created_at->format('d/m/Y'),
                    'total' => $devis->total(),
                    'nombre_composants' => $devis->composants->count(),
                    'batiment' => [
                        'nom' => $devis->batiment->nom,
                    ],
                    'projet' => [
                        'nom' => $devis->batiment->projet->nom,
                    ],
                    'organisation' => [
                        'nom' => $devis->batiment->projet->organisation->nom,
                        'is_system' => $devis->batiment->projet->organisation->is_system ?? false,
                    ],
                ];
            });

        return response()->json($templates);
    }

    /**
     * Afficher le formulaire de réutilisation d'un template
     */
    public function reuse(DevisEstimatif $template)
    {
        // Vérifier que c'est bien un template
        if (!$template->is_template) {
            abort(404, 'Ce devis n\'est pas un template.');
        }

        $this->validatePermission('ORG_DEVIS_ESTIMATIF_VIEW');

        $batimentSource = $template->batiment;

        if (!$batimentSource || !$batimentSource->projet) {
            return back()->withErrors([
                'template' => 'Le template n\'est rattaché à aucun bâtiment ou projet valide.'
            ]);
        }

        $template->load([
            'batiment.projet.organisation',
            'composants.niveau',
            'composants.unite',
        ]);

        // Récupérer tous les projets de l'organisation active
        $activeOrganisationId = getPermissionsTeamId();

        $projets = \App\Models\Projet::where('organisation_id', $activeOrganisationId)
            ->orderBy('nom')
            ->get()
            ->map(fn($projet) => [
                'id' => $projet->id,
                'nom' => $projet->nom,
                'code' => $projet->code ?? '',
            ]);

        $composantsTemplate = $template->composants
            ->groupBy('niveau_id')
            ->map(fn($items, $niveauId) => [
                'niveau_id' => (int) $niveauId,
                'composants' => $items->map(fn($comp) => [
                    'code' => $comp->code,
                    'piece' => $comp->piece,
                    'unite_id' => $comp->unite_id,
                    'qte' => (float) $comp->qte,
                    'prix_unitaire' => (float) $comp->prix_unitaire,
                ])->values()->all(),
            ])
            ->values()
            ->all();

        return Inertia::render('Templates/DevisEstimatif/Reuse', [
            'template' => [
                'id' => $template->id,
                'code' => $template->code,
                'intitule' => $template->intitule,
                'statut' => $template->statut,
            ],

            'batimentSource' => [
                'id' => $batimentSource->id,
                'nom' => $batimentSource->nom,
                'code' => $batimentSource->code ?? '',
            ],

            'projetSource' => [
                'id' => $batimentSource->projet->id,
                'nom' => $batimentSource->projet->nom,
                'code' => $batimentSource->projet->code ?? '',
            ],

            'composantsSource' => $composantsTemplate,

            'projets' => $projets,

            'niveaux' => NiveauBatiment::orderBy('code')->get(),
            'unites' => UniteMesure::orderBy('libelle')->get(),

            'devise' => $batimentSource->projet->devise->libelle ?? 'MAD',

            // IMPORTANT: Passer les permissions et l'utilisateur pour la sidebar
            'auth' => [
                'user' => Auth::user(),
                'permissions' => Auth::user()?->getAllPermissions()->pluck('name')->toArray() ?? [],
            ],
        ]);
    }

    /**
     * Créer un nouveau devis à partir d'un template
     */
    public function storeReuse(Request $request, DevisEstimatif $template)
    {
        if (!$template->is_template) {
            return redirect()
                ->route('templates.index')
                ->with('error', 'Ce devis n\'est pas un template.');
        }

        $this->validatePermission('ORG_DEVIS_ESTIMATIF_CREATE');

        if (!$template->batiment || !$template->batiment->projet) {
            return back()->withErrors([
                'template_source' => 'Le template n\'est rattaché à aucun bâtiment ou projet.'
            ]);
        }

        $request->validate([
            'batiment_id' => [
                'required',
                'exists:batiments,id',
                Rule::unique('devis_estimatif', 'batiment_id')
            ],
            'intitule' => 'required|string|max:255',
            'niveaux' => 'required|array|min:1',
            'niveaux.*.niveau_id' => 'required|exists:niveaux_batiment,id',
            'niveaux.*.composants' => 'required|array|min:1',
            'niveaux.*.composants.*.code' => 'required|string|max:50',
            'niveaux.*.composants.*.piece' => 'required|string|max:255',
            'niveaux.*.composants.*.unite_id' => 'required|exists:unites_mesure,id',
            'niveaux.*.composants.*.qte' => 'required|numeric|min:0',
            'niveaux.*.composants.*.prix_unitaire' => 'required|numeric|min:0',
        ]);

        $batiment = \App\Models\Batiment::findOrFail($request->batiment_id);

        if (!$batiment->projet) {
            return back()->withErrors(['batiment_id' => 'Le projet du bâtiment sélectionné est introuvable.']);
        }

        $activeOrganisationId = getPermissionsTeamId();
        if ($batiment->projet->organisation_id !== $activeOrganisationId) {
            abort(403, 'Accès refusé à ce bâtiment.');
        }

        return DB::transaction(function () use ($request, $batiment, $template) {

            $newDevis = DevisEstimatif::create([
                'intitule' => $request->intitule,
                'batiment_id' => $request->batiment_id,
                'code' => $this->generateCode(),
                'statut' => 'brouillon',
                'is_template' => false,
                'created_by' => Auth::id(),
            ]);

            $codesUtilises = [];
            foreach ($request->niveaux as $niveau) {
                foreach ($niveau['composants'] as $comp) {
                    if (in_array($comp['code'], $codesUtilises)) {
                        throw ValidationException::withMessages([
                            'code' => "Le code {$comp['code']} est déjà utilisé dans ce devis."
                        ]);
                    }

                    $codesUtilises[] = $comp['code'];

                    $montant = bcmul($comp['qte'], $comp['prix_unitaire'], 2);

                    \App\Models\ComposantNiveau::create([
                        'devis_estimatif_id' => $newDevis->id,
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

            Log::info('Devis créé à partir d\'un template', [
                'template_id' => $template->id,
                'template_code' => $template->code,
                'nouveau_devis_id' => $newDevis->id,
                'nouveau_devis_code' => $newDevis->code,
                'batiment_destination_id' => $batiment->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()
                ->route('batiments.devisestimatif.show', [
                    'batiment' => $batiment->id,
                    'devisestimatif' => $newDevis->id, // <- Obligatoire !
                ])
                ->with('success', "Devis créé avec succès à partir du template {$template->code}.");


        });
    }

    /**
     * Générer automatiquement le code devis
     */
    private function generateCode(): string
    {
        $lastId = DevisEstimatif::max('id') ?? 0;
        return 'DEV-' . date('Y') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Valider les permissions de l'utilisateur
     */
    protected function validatePermission(string $permission): void
    {
        $activeOrganisationId = getPermissionsTeamId();

        if (!OrganisationContext::hasPermission(Auth::user(), $activeOrganisationId, $permission)) {
            abort(Response::HTTP_FORBIDDEN, "Vous n'avez pas cette permission");
        }
    }

    /**
     * Récupérer les bâtiments disponibles d'un projet (sans devis)
     */
    public function getBatimentsDisponibles(\App\Models\Projet $projet)
    {
        $this->validatePermission('ORG_DEVIS_ESTIMATIF_VIEW');

        // Vérifier que le projet appartient à l'organisation active
        $activeOrganisationId = getPermissionsTeamId();
        if ($projet->organisation_id !== $activeOrganisationId) {
            abort(403, 'Accès refusé à ce projet.');
        }

        $batiments = \App\Models\Batiment::where('projet_id', $projet->id)
            ->whereDoesntHave('devisEstimatif')
            ->orderBy('code')
            ->get()
            ->map(fn($batiment) => [
                'id' => $batiment->id,
                'code' => $batiment->code,
                'nom' => $batiment->nom,
            ]);

        return response()->json($batiments);
    }
}