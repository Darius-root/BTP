<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ClientController extends Controller
{
    /**
     * Organisation active (EN DUR)
     */
    private const ACTIVE_ORGANISATION_ID = 2;

    /**
     * Liste des clients de l'organisation active uniquement
     */
    public function index(): Response
    {
        $clients = Client::with('organisation')
            ->where('organisation_id', self::ACTIVE_ORGANISATION_ID)
            ->latest()
            ->paginate(10);

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
        ]);
    }

    /**
     * Formulaire de création (organisation imposée)
     */
    public function create(): Response
    {
        return Inertia::render('Clients/Create');
    }

    /**
     * Création d'un client rattaché à l'organisation active
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'societe' => 'nullable|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'telephone' => 'nullable|string|max:255',
            'adresse' => 'nullable|string',
        ]);

        $validated['organisation_id'] = self::ACTIVE_ORGANISATION_ID;

        Client::create($validated);

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client créé avec succès.');
    }

    /**
     * Affichage d'un client (uniquement s'il appartient à l'organisation active)
     */
    public function show(Client $client): Response
    {
        if ($client->organisation_id !== self::ACTIVE_ORGANISATION_ID) {
            abort(403, 'Client non accessible.');
        }

        $client->load(['organisation', 'projets']);

        return Inertia::render('Clients/Show', [
            'client' => $client,
        ]);
    }

    /**
     * Formulaire d'édition (organisation active uniquement)
     */
    public function edit(Client $client): Response
    {
        if ($client->organisation_id !== self::ACTIVE_ORGANISATION_ID) {
            abort(403);
        }

        return Inertia::render('Clients/Edit', [
            'client' => $client,
        ]);
    }

    /**
     * Mise à jour d'un client de l'organisation active
     */
    public function update(Request $request, Client $client): RedirectResponse
    {
        if ($client->organisation_id !== self::ACTIVE_ORGANISATION_ID) {
            abort(403);
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
    }

    /**
     * Suppression d'un client de l'organisation active
     */
    public function destroy(Client $client): RedirectResponse
    {
        if ($client->organisation_id !== self::ACTIVE_ORGANISATION_ID) {
            abort(403);
        }

        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client supprimé avec succès.');
    }
}
