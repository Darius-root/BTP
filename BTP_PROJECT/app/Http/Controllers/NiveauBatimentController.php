<?php

namespace App\Http\Controllers;

use App\Models\NiveauBatiment;
use App\Models\Batiment;
use App\Services\OrganisationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Throwable;

class NiveauBatimentController extends Controller
{
    /**
     * Liste globale des niveaux de l'organisation active
     */
    public function index()
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_NIVEAU_VIEW')) {
                return back()->with('error', "Vous n'avez pas la permission de consulter les niveaux.");
            }

            $niveaux = NiveauBatiment::with(['batiment.projet', 'user:id,name'])
                ->whereHas('batiment.projet', fn($q) => $q->where('organisation_id', $activeOrg))
                ->latest()
                ->paginate(10);

            return Inertia::render('Organisations/NiveauxBatiment/Index', [
                'niveaux' => $niveaux,
                'activeOrganisation' => $activeOrg,
            ]);

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Liste des niveaux d'un bâtiment précis
     */
    public function indexByBatiment(Batiment $batiment)
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if ($batiment->projet->organisation_id !== $activeOrg) {
                return back()->with('error', 'Bâtiment non accessible.');
            }

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_NIVEAU_VIEW')) {
                return back()->with('error', "Vous n'avez pas la permission de consulter les niveaux.");
            }

            $niveaux = NiveauBatiment::where('batiment_id', $batiment->id)
                ->with(['batiment', 'user:id,name'])
                ->latest()
                ->paginate(10);

            return Inertia::render('Organisations/NiveauxBatiment/Index', [
                'niveaux' => $niveaux,
                'batiment' => $batiment->load('projet'),
                'activeOrganisation' => $activeOrg,
            ]);

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Création globale
     */
    public function create()
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_NIVEAU_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer un niveau.");
            }

            $batiments = Batiment::whereHas('projet', fn($q) => $q->where('organisation_id', $activeOrg))
                ->with('projet')
                ->get();

            return Inertia::render('Organisations/NiveauxBatiment/Create', [
                'batiments' => $batiments,
                'activeOrganisation' => $activeOrg,
            ]);

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Création depuis un bâtiment précis
     */
    public function createFromBatiment(Batiment $batiment)
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if ($batiment->projet->organisation_id !== $activeOrg) {
                return back()->with('error', 'Bâtiment non accessible.');
            }

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_NIVEAU_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer un niveau.");
            }
            $niveaux = NiveauBatiment::where('batiment_id', $batiment->id)
                ->with(['batiment.projet', 'user:id,name'])
                ->latest()
                ->paginate(10);

            return Inertia::render('Organisations/NiveauxBatiment/Create', [
                'batiment' => $batiment->load('projet'),
                'activeOrganisation' => $activeOrg,
                'niveaux' => $niveaux
            ]);

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Enregistrement
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_NIVEAU_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer un niveau.");
            }

            $validated = $request->validate([
                'code' => 'required|string|max:255|unique:niveaux_batiment,code',
                'nom' => 'required|string|max:255|unique:niveaux_batiment,nom',
                'description' => 'nullable|string',
                'batiment_id' => 'required|exists:batiments,id',
            ]);

            $batiment = Batiment::findOrFail($validated['batiment_id']);

            if ($batiment->projet->organisation_id !== $activeOrg) {
                return back()->with('error', 'Bâtiment non autorisé.');
            }

            $validated['user_id'] = Auth::id();

            $niveau = NiveauBatiment::create($validated);

            return redirect()
                ->route('batiments.niveaux.index', $niveau->batiment_id)
                ->with('success', 'Niveau créé avec succès.');

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Détail
     */
    public function show(NiveauBatiment $niveauBatiment)
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if ($niveauBatiment->batiment->projet->organisation_id !== $activeOrg) {
                return back()->with('error', 'Niveau non accessible.');
            }

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_NIVEAU_VIEW')) {
                return back()->with('error', "Vous n'avez pas la permission de consulter ce niveau.");
            }

            return Inertia::render('Organisations/NiveauxBatiment/Show', [
                'niveau' => $niveauBatiment->load(['batiment.projet', 'user:id,name']),
                'activeOrganisation' => $activeOrg,
            ]);

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Édition
     */
    public function edit(NiveauBatiment $niveauBatiment)
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if ($niveauBatiment->batiment->projet->organisation_id !== $activeOrg) {
                return back()->with('error', 'Accès interdit.');
            }

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_NIVEAU_EDIT')) {
                return back()->with('error', "Vous n'avez pas la permission de modifier ce niveau.");
            }

            $batiments = Batiment::whereHas('projet', fn($q) => $q->where('organisation_id', $activeOrg))
                ->with('projet')
                ->get();

            return Inertia::render('Organisations/NiveauxBatiment/Edit', [
                'niveau' => $niveauBatiment->load('batiment'),
                'batiments' => $batiments,
                'activeOrganisation' => $activeOrg,
            ]);

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, NiveauBatiment $niveauBatiment): RedirectResponse
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if ($niveauBatiment->batiment->projet->organisation_id !== $activeOrg) {
                return back()->with('error', 'Accès interdit.');
            }

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_NIVEAU_EDIT')) {
                return back()->with('error', "Vous n'avez pas la permission de modifier ce niveau.");
            }

            $validated = $request->validate([
                'code' => 'required|string|max:255|unique:niveaux_batiment,code,' . $niveauBatiment->id,
                'nom' => 'required|string|max:255|unique:niveaux_batiment,nom,' . $niveauBatiment->id,
                'description' => 'nullable|string',
                'batiment_id' => 'required|exists:batiments,id',
            ]);

            $batiment = Batiment::findOrFail($validated['batiment_id']);

            if ($batiment->projet->organisation_id !== $activeOrg) {
                return back()->with('error', 'Bâtiment non autorisé.');
            }

            $niveauBatiment->update($validated);

            return redirect()
                ->route('batiments.niveaux.index', $niveauBatiment->batiment_id)
                ->with('success', 'Niveau mis à jour avec succès.');

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Suppression
     */
    public function destroy(NiveauBatiment $niveauBatiment): RedirectResponse
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if ($niveauBatiment->batiment->projet->organisation_id !== $activeOrg) {
                return back()->with('error', 'Suppression non autorisée.');
            }

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_NIVEAU_DELETE')) {
                return back()->with('error', "Vous n'avez pas la permission de supprimer ce niveau.");
            }

            $batimentId = $niveauBatiment->batiment_id;
            $niveauBatiment->delete();

            return redirect()
                ->route('batiments.niveaux.index', $batimentId)
                ->with('success', 'Niveau supprimé avec succès.');

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}