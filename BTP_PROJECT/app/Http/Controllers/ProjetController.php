<?php

namespace App\Http\Controllers;

use App\Models\Projet;
use App\Models\Client;
use App\Models\Organisation;
use App\Models\Devise;
use App\Services\OrganisationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProjetController extends Controller
{
    /**
     * Récupère l'organisation active depuis la session
     */


    /**
     * Liste des projets de l'organisation active
     */
    public function index()
    {
        try {
            $activeOrgId = getPermissionsTeamId();

            if (OrganisationContext::hasPermission(Auth::user(), $activeOrgId, 'ORG_PROJET_VIEW') === false) {
                return back()->with('error', "Vous n'avez pas la permission de voir les projets.");
            }

            $projets = Projet::with(['client', 'organisation', 'devise'])
                ->where('organisation_id', $activeOrgId)
                ->latest()
                ->paginate(10);

            // Récupérer l'organisation complète
            $activeOrganisation = Organisation::find($activeOrgId);

            return Inertia::render('Organisations/Projets/Index', [
                'projets' => $projets,
                'activeOrganisation' => $activeOrganisation, // Objet complet
            ]);

        } catch (Throwable $e) {
            Log::error('Erreur index projets: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_PROJET_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer un projet.");
            }

            return Inertia::render('Organisations/Projets/Create', [
                'clients' => Client::where('organisation_id', $activeOrg)->get(),
                'organisations' => Organisation::whereKey($activeOrg)->get(),
                'devises' => Devise::all(),
                'activeOrganisation' => $activeOrg,
            ]);

        } catch (Throwable $e) {
            Log::error('Erreur create projets: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Enregistrement d'un projet
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_PROJET_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer un projet.");
            }

            // Log des données reçues
            Log::info('Données reçues pour création projet', $request->all());

            $validated = $request->validate([
                'nom' => 'required|string|max:255|unique:projets,nom',
                'client_id' => 'required|exists:clients,id',
                'devise_id' => 'nullable|exists:devises,id',
                'tva' => 'required|integer|min:0|max:100',
                'localisation' => 'nullable|string|max:255',
                'resume' => 'nullable|string',
                'budget_previsionnel' => 'nullable|numeric|min:0',
                'type_projet' => 'nullable|string|max:255',
            ]);


            DB::beginTransaction();

            // Vérification client ∈ organisation active
            $clientExists = Client::where('id', $validated['client_id'])
                ->where('organisation_id', $activeOrg)
                ->exists();


            if (!$clientExists) {
                DB::rollBack();
                return back()->withInput()->withErrors([
                    'client_id' => "Ce client n'appartient pas à l'organisation active.",
                ]);
            }

            $validated['organisation_id'] = $activeOrg;

            try {
                $validated['code_projet'] = $this->generateCodeProjet(
                    $validated['nom'],
                    $activeOrg
                );
            } catch (Throwable $e) {
                DB::rollBack();
                return back()
                    ->withInput()
                    ->withErrors([
                        'global' => 'Erreur lors de la génération du code projet: ' . $e->getMessage(),
                    ]);
            }


            $projet = Projet::create($validated);


            DB::commit();

            return redirect()
                ->route('projets.index')
                ->with('success', 'Projet créé avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());

        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Erreur création projet: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->withErrors([
                    'global' => 'Erreur lors de la création du projet.',
                    'exception' => $e->getMessage(),
                ]);
        }
    }

    /**
     * Détail d'un projet
     */
    public function show(Projet $projet)
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_PROJET_VIEW')) {
                return back()->with('error', "Vous n'avez pas la permission de consulter ce projet.");
            }
            if ($projet->organisation_id !== $activeOrg) {
                return redirect()
                    ->route('projets.index')
                    ->with('error', 'Accès interdit à ce projet.');
            }

            $projet->load(['client', 'organisation', 'devise', 'batiments']);

            return Inertia::render('Organisations/Projets/Show', [
                'projet' => $projet,
                'activeOrganisation' => $activeOrg,
            ]);

        } catch (Throwable $e) {
            Log::error('Erreur show projet: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Projet $projet)
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_PROJET_EDIT')) {
                return back()->with('error', "Vous n'avez pas la permission de modifier ce projet.");
            }
            if ($projet->organisation_id !== $activeOrg) {
                return redirect()
                    ->route('projets.index')
                    ->with('error', 'Accès interdit à ce projet.');
            }

            return Inertia::render('Organisations/Projets/Edit', [
                'projet' => $projet,
                'clients' => Client::where('organisation_id', $activeOrg)->get(),
                'organisations' => Organisation::whereKey($activeOrg)->get(),
                'devises' => Devise::all(),
                'activeOrganisation' => $activeOrg,
            ]);

        } catch (Throwable $e) {
            Log::error('Erreur edit projet: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, Projet $projet): RedirectResponse
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_PROJET_EDIT')) {
                return back()->with('error', "Vous n'avez pas la permission de modifier ce projet.");
            }
            if ($projet->organisation_id !== $activeOrg) {
                return back()->with('error', 'Accès interdit à ce projet.');
            }

            $validated = $request->validate([
                'nom' => 'required|string|max:255|unique:projets,nom,' . $projet,
                'client_id' => 'required|exists:clients,id',
                'devise_id' => 'nullable|exists:devises,id',
                'tva' => 'required|integer|min:0|max:100',
                'localisation' => 'nullable|string|max:255',
                'resume' => 'nullable|string',
                'budget_previsionnel' => 'nullable|numeric|min:0',
                'type_projet' => 'nullable|string|max:255',
            ]);

            // Vérification que le client appartient à l'organisation active
            if (
                !Client::where('id', $validated['client_id'])
                    ->where('organisation_id', $activeOrg)
                    ->exists()
            ) {
                return back()->withInput()->withErrors([
                    'client_id' => 'Ce client n\'appartient pas à l\'organisation active.',
                ]);
            }

            $projet->update($validated);

            return redirect()
                ->route('projets.index')
                ->with('success', 'Projet mis à jour avec succès.');

        } catch (Throwable $e) {
            Log::error('Erreur update projet: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors([
                    'global' => 'Erreur lors de la mise à jour.',
                    'exception' => $e->getMessage(),
                ]);
        }
    }

    /**
     * Suppression
     */
    public function destroy(Projet $projet): RedirectResponse
    {
        try {
            $activeOrg = getPermissionsTeamId();
            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_PROJET_DELETE')) {
                return back()->with('error', "Vous n'avez pas la permission de supprimer ce projet.");
            }

            if ($projet->organisation_id !== $activeOrg) {
                return back()->with('error', 'Suppression non autorisée.');
            }

            // Sécurité métier optionnelle
            if ($projet->batiments()->exists()) {
                return back()->with(
                    'error',
                    'Impossible de supprimer ce projet : il est lié à des bâtiments.'
                );
            }

            $projet->delete();

            return redirect()
                ->route('projets.index')
                ->with('success', 'Projet supprimé avec succès.');

        } catch (Throwable $e) {
            Log::error('Erreur destroy projet: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Génération du code projet : AAA-BBB-001
     */
    private function generateCodeProjet(string $nomProjet, int $organisationId): string
    {
        $organisation = Organisation::findOrFail($organisationId);

        $orgCode = $this->extractCode($organisation->nom);
        $projectCode = $this->extractCode($nomProjet);

        $prefix = "{$orgCode}-{$projectCode}-";

        $last = Projet::where('organisation_id', $organisationId)
            ->where('code_projet', 'LIKE', $prefix . '%')
            ->orderByDesc('code_projet')
            ->first();

        $next = $last
            ? ((int) Str::afterLast($last->code_projet, '-') + 1)
            : 1;

        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Extrait 3 lettres significatives
     */
    private function extractCode(string $text): string
    {
        $words = collect(preg_split('/\s+/', strtoupper($text)))
            ->filter()
            ->values();

        if ($words->count() === 1) {
            return Str::padRight(Str::substr($words[0], 0, 3), 3, 'X');
        }

        return Str::padRight(
            $words->map(fn($w) => Str::substr($w, 0, 1))->implode(''),
            3,
            'X'
        );
    }
}
