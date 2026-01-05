<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class OrganisationController extends Controller
{
    private const ACTIVE_ORGANISATION_ID = 2;

    public function index(): Response
    {
        $organisations = Organisation::with('user')
            ->latest()
            ->paginate(10)
            ->through(function ($organisation) {
                return [
                    'id' => $organisation->id,
                    'name' => $organisation->name,
                    'raison_sociale' => $organisation->raison_sociale,
                    'logo' => $organisation->logo,
                    'adresse' => $organisation->adresse,
                    'pays' => $organisation->pays,
                    'devise' => $organisation->devise,
                    'user_id' => $organisation->user_id,
                    'created_at' => $organisation->created_at,
                    'updated_at' => $organisation->updated_at,
                    'is_active' => $organisation->id === self::ACTIVE_ORGANISATION_ID,
                ];
            });

        return Inertia::render('Organisations/Index', [
            'organisations' => $organisations,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Organisations/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:organisations,name',
            'raison_sociale' => 'required|string|max:255|unique:organisations,raison_sociale',
            'logo' => 'nullable|image|max:2048',
            'adresse' => 'nullable|string',
            'pays' => 'nullable|string|max:255',
            'devise' => 'nullable|string|max:3',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $validated['user_id'] = $request->user()->id;

        Organisation::create($validated);

        return redirect()->route('organisations.index')
            ->with('success', 'Organisation créée avec succès.');
    }

    public function show(Organisation $organisation): Response
    {
        $organisation->load(['user', 'clients', 'projets']);

        return Inertia::render('Organisations/Show', [
            'organisation' => $organisation
        ]);
    }

    public function edit(Organisation $organisation): Response
    {
        return Inertia::render('Organisations/Edit', [
            'organisation' => $organisation
        ]);
    }

    public function update(Request $request, Organisation $organisation): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:organisations,name,' . $organisation->id,
            'raison_sociale' => 'required|string|max:255|unique:organisations,raison_sociale,' . $organisation->id,
            'logo' => 'nullable|image|max:2048',
            'adresse' => 'nullable|string',
            'pays' => 'nullable|string|max:255',
            'devise' => 'nullable|string|max:3',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $organisation->update($validated);

        return redirect()->route('organisations.index')
            ->with('success', 'Organisation mise à jour avec succès.');
    }

    public function destroy(Organisation $organisation): RedirectResponse
    {
        $organisation->delete();

        return redirect()->route('organisations.index')
            ->with('success', 'Organisation supprimée avec succès.');
    }
}
