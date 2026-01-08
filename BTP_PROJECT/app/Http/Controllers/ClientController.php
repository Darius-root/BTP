<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Organisation;
use App\Services\OrganisationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Throwable;

class ClientController extends Controller
{
    /**
     * Liste des clients de l'organisation active
     */
    public function index()
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_CLIENT_VIEW')) {
                return back()->with('error', "Vous n'avez pas la permission de voir les clients.");
            }

            $clients = Client::with('organisation')
                ->where('organisation_id', $activeOrg)
                ->latest()
                ->paginate(10);

            return Inertia::render('Clients/Index', [
                'clients' => $clients,
                'activeOrganisation' => $activeOrg,
            ]);

        } catch (Throwable $e) {
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

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_CLIENT_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer un client.");
            }

            return Inertia::render('Clients/Create', [
                'activeOrganisation' => $activeOrg,
            ]);

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Création
     */
    public function store(Request $request)
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_CLIENT_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission de créer un client.");
            }

            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'societe' => 'nullable|string|max:255',
                'email' => 'required|email|unique:clients,email',
                'telephone' => 'nullable|string|max:255',
                'adresse' => 'nullable|string',
            ]);

            $validated['organisation_id'] = $activeOrg;

            Client::create($validated);

            return redirect()
                ->route('clients.index')
                ->with('success', 'Client créé avec succès.');

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Détail
     */
    public function show(Client $client)
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_CLIENT_VIEW')) {
                return back()->with('error', "Vous n'avez pas la permission de consulter ce client.");
            }

            if ($client->organisation_id !== $activeOrg) {
                return redirect()
                    ->route('clients.index')
                    ->with('error', 'Client non accessible.');
            }

            $client->load(['organisation', 'projets']);

            return Inertia::render('Clients/Show', [
                'client' => $client,
                'activeOrganisation' => $activeOrg,
            ]);

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Formulaire d’édition
     */
    public function edit(Client $client)
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_CLIENT_EDIT')) {
                return back()->with('error', "Vous n'avez pas la permission de modifier ce client.");
            }

            if ($client->organisation_id !== $activeOrg) {
                return redirect()
                    ->route('clients.index')
                    ->with('error', 'Accès interdit.');
            }

            return Inertia::render('Clients/Edit', [
                'client' => $client->load('organisation'),
                'activeOrganisation' => $activeOrg,
            ]);

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, Client $client)
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_CLIENT_EDIT')) {
                return back()->with('error', "Vous n'avez pas la permission de modifier ce client.");
            }

            if ($client->organisation_id !== $activeOrg) {
                return redirect()
                    ->route('clients.index')
                    ->with('error', 'Accès interdit.');
            }

            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'societe' => 'nullable|string|max:255',
                'email' => 'required|email|unique:clients,email,' . $client->id,
                'telephone' => 'nullable|string|max:255',
                'adresse' => 'nullable|string',
            ]);

            $client->update($validated);

            return redirect()
                ->route('clients.index')
                ->with('success', 'Client mis à jour avec succès.');

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Suppression
     */
    public function destroy(Client $client)
    {
        try {
            $activeOrg = getPermissionsTeamId();

            if (!OrganisationContext::hasPermission(Auth::user(), $activeOrg, 'ORG_CLIENT_DELETE')) {
                return back()->with('error', "Vous n'avez pas la permission de supprimer ce client.");
            }

            if ($client->organisation_id !== $activeOrg) {
                return redirect()
                    ->route('clients.index')
                    ->with('error', 'Suppression non autorisée.');
            }

            if ($client->projets()->exists()) {
                return back()->with(
                    'error',
                    'Impossible de supprimer ce client : il est lié à des projets.'
                );
            }

            $client->delete();

            return redirect()
                ->route('clients.index')
                ->with('success', 'Client supprimé avec succès.');

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
