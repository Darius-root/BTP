<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;

class ClientController extends Controller
{
    /**
     * Liste des clients de l'organisation active uniquement
     */

    private function getActiveOrganisation(): Organisation
    {
        $organisation = session('active_organisation');

        if (!$organisation instanceof Organisation) {
            abort(403, 'Aucune organisation active.');
        }

        // Synchronisation Spatie (IMPORTANT)
        setPermissionsTeamId($organisation->id);

        return $organisation;
    }

    public function index()
    {
        try {
            $activeOrg = $this->getActiveOrganisation();

            $clients = Client::with('organisation')
                ->where('organisation_id', $activeOrg->id)
                ->latest()
                ->paginate(10);

            return Inertia::render('Clients/Index', [
                'clients' => $clients,
                'activeOrganisation' => $activeOrg,
            ]);

        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    /**
     * Formulaire de création (organisation active imposée)
     */
    public function create()
    {
        try {
            $activeOrg = $this->getActiveOrganisation();

            return Inertia::render('Clients/Create', [
                'activeOrganisation' => $activeOrg,
            ]);

        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    /**
     * Création d'un client rattaché à l'organisation active
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $activeOrg = $this->getActiveOrganisation();

            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'societe' => 'nullable|string|max:255',
                'email' => 'required|email|unique:clients,email',
                'telephone' => 'nullable|string|max:255',
                'adresse' => 'nullable|string',
            ]);

            $validated['organisation_id'] = $activeOrg->id;

            Client::create($validated);

            return redirect()
                ->route('clients.index')
                ->with('success', 'Client créé avec succès.');

        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    /**
     * Affichage d'un client (uniquement si organisation active)
     */
    public function show(Client $client)
    {
        try {
            $activeOrg = $this->getActiveOrganisation();

            if ($client->organisation_id !== $activeOrg->id) {
                return redirect()
                    ->route('clients.index')
                    ->with('error', 'Client non accessible.');
            }

            $client->load(['organisation', 'projets']);

            return Inertia::render('Clients/Show', [
                'client' => $client,
                'activeOrganisation' => $activeOrg,
            ]);

        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    /**
     * Formulaire d'édition (organisation active uniquement)
     */
    public function edit(Client $client)
    {
        try {
            $activeOrg = $this->getActiveOrganisation();

            if ($client->organisation_id !== $activeOrg->id) {
                return redirect()
                    ->route('clients.index')
                    ->with('error', 'Vous ne pouvez pas modifier un client hors de l’organisation active.');
            }

            return Inertia::render('Clients/Edit', [
                'client' => $client->load('organisation'),
                'activeOrganisation' => $activeOrg,
            ]);

        } catch (\Throwable $e) {
            return back()->with('error', 'Impossible d’ouvrir le formulaire : ' . $e->getMessage());
        }
    }



    /**
     * Mise à jour d'un client de l'organisation active
     */
    public function update(Request $request, Client $client): RedirectResponse
    {
        try {
            $activeOrg = $this->getActiveOrganisation();

            if ($client->organisation_id !== $activeOrg->id) {
                return redirect()
                    ->route('clients.index')
                    ->with('error', 'Vous ne pouvez pas modifier ce client.');
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

        } catch (\Throwable $e) {
            return back()->with('error', 'Impossible de mettre à jour le client : ' . $e->getMessage());
        }
    }


    /**
     * Suppression d'un client de l'organisation active
     */
    public function destroy(Client $client): RedirectResponse
    {
        try {
            $activeOrg = $this->getActiveOrganisation();

            if ($client->organisation_id !== $activeOrg->id) {
                return redirect()
                    ->route('clients.index')
                    ->with('error', 'Vous ne pouvez pas supprimer ce client.');
            }

            // Sécurité métier optionnelle
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

        } catch (\Throwable $e) {
            return back()->with('error', 'Impossible de supprimer le client : ' . $e->getMessage());
        }
    }

}
