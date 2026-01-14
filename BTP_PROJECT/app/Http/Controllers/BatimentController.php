<?php

namespace App\Http\Controllers;

use App\Models\Batiment;
use App\Models\Projet;
use App\Services\OrganisationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Throwable;

class BatimentController extends Controller
{
    /**
     * Liste globale des bâtiments de l'organisation active
     */
   

    /**
     * Liste des bâtiments d'un projet précis
     */
    public function indexByProjet(Projet $projet)
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if ($projet->organisation_id !== $activeOrg) {
                return back()->with('error', 'Projet non accessible.');
            }

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_BATIMENT_VIEW')) {
                return back()->with('error', "Vous n'avez pas la permission de consulter les bâtiments.");
            }

            $batiments = Batiment::where('projet_id', $projet->id)
                ->with('projet')
                ->latest()
                ->paginate(10);

            return Inertia::render('Organisations/Batiments/Index', [
                'batiments' => $batiments,
                'projet' => $projet,
                'activeOrganisation' => $activeOrg,
            ]);

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }



    /**
     * Création depuis un projet précis
     */
    public function createFromProjet(Projet $projet)
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if ($projet->organisation_id !== $activeOrg) {
                return back()->with('error', 'Projet non accessible.');
            }

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_BATIMENT_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer un bâtiment.");
            }

            return Inertia::render('Organisations/Batiments/Create', [
                'projet' => $projet,
                'activeOrganisation' => $activeOrg,
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

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_BATIMENT_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer un bâtiment.");
            }

            $validated = $request->validate([
                'code' => 'required|string|max:255|unique:batiments,code',
                'nom' => 'required|string|max:255',
                'localisation' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'projet_id' => 'required|exists:projets,id',
            ]);

            $projet = Projet::findOrFail($validated['projet_id']);

            if ($projet->organisation_id !== $activeOrg) {
                return back()->with('error', 'Projet non autorisé.');
            }

            $batiment = Batiment::create($validated);

            return redirect()
                ->route('projets.batiments.index', $batiment->projet_id)
                ->with('success', 'Bâtiment créé avec succès.');

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Détail
     */
    public function show(Batiment $batiment)
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if ($batiment->projet->organisation_id !== $activeOrg) {
                return back()->with('error', 'Bâtiment non accessible.');
            }

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_BATIMENT_VIEW')) {
                return back()->with('error', "Vous n'avez pas la permission de consulter ce bâtiment.");
            }

            return Inertia::render('Organisations/Batiments/Show', [
                'batiment' => $batiment->load('projet'),
                'activeOrganisation' => $activeOrg,
            ]);

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Édition
     */
    public function edit(Batiment $batiment)
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if ($batiment->projet->organisation_id !== $activeOrg) {
                return back()->with('error', 'Accès interdit.');
            }

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_BATIMENT_EDIT')) {
                return back()->with('error', "Vous n'avez pas la permission de modifier ce bâtiment.");
            }

            // Charger la relation projet pour l'afficher
            $batiment->load('projet');

            return Inertia::render('Organisations/Batiments/Edit', [
                'batiment' => $batiment,
                'activeOrganisation' => $activeOrg,
            ]);

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, Batiment $batiment): RedirectResponse
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if ($batiment->projet->organisation_id !== $activeOrg) {
                return back()->with('error', 'Accès interdit.');
            }

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_BATIMENT_EDIT')) {
                return back()->with('error', "Vous n'avez pas la permission de modifier ce bâtiment.");
            }

            $validated = $request->validate([
                'code' => 'required|string|max:255|unique:batiments,code,' . $batiment->id,
                'nom' => 'required|string|max:255',
                'localisation' => 'nullable|string|max:255',
                'description' => 'nullable|string',
            ]);

            $batiment->update($validated);

            return redirect()
                ->route('projets.batiments.index', $batiment->projet_id)
                ->with('success', 'Bâtiment mis à jour avec succès.');

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Suppression
     */
    public function destroy(Batiment $batiment): RedirectResponse
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if ($batiment->projet->organisation_id !== $activeOrg) {
                return back()->with('error', 'Suppression non autorisée.');
            }

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_BATIMENT_DELETE')) {
                return back()->with('error', "Vous n'avez pas la permission de supprimer ce bâtiment.");
            }

            $projetId = $batiment->projet_id;
            $batiment->delete();

            return redirect()
                ->route('projets.batiments.index', $projetId)
                ->with('success', 'Bâtiment supprimé avec succès.');

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
