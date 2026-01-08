<?php

namespace App\Http\Controllers;

use App\Models\Organisation;

use App\Services\OrganisationService;
use Auth;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Response;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OrganisationController extends Controller
{
    public function index(OrganisationService $organisationService)
    {

        
        $user = Auth::user();
        $currentTeamId = getPermissionsTeamId();

        // ===== Vérification SUPER ADMIN dans le contexte SYSTEM =====
        setPermissionsTeamId($organisationService->system());
        $user->unsetRelation('roles')->unsetRelation('permissions');

        $isSystemAdmin = $user->hasRole('SYSTEM_ADMIN_PLATEFORME');

        // ===== Restauration immédiate =====
        setPermissionsTeamId($currentTeamId);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        // ============================================================

        if ($isSystemAdmin) {
            $organisations = Organisation::where('is_system', false)->get();

            return Inertia::render('Organisations/Index', [
                'organisations' => $organisations,
            ]);
        }

        // ===== USER NORMAL =====
        $organisations = $user->organisations()
            ->where('is_system', false)
            ->get();

        $organisationsWithRoles = [];

        foreach ($organisations as $organisation) {
            setPermissionsTeamId($organisation->id);
            $user->unsetRelation('roles')->unsetRelation('permissions');

            $organisationsWithRoles[] = [
                'team' => $organisation,
                'roles' => $user->getRoleNames(),
                'statut' => $organisation->id === $currentTeamId,
            ];
        }

        setPermissionsTeamId($currentTeamId);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        return Inertia::render('Organisations/Index', [
            'user' => $user,
            'teamsWithRoles' => $organisationsWithRoles,
        ]);
    }








    public function activate(Organisation $organisation)
    {
        $user = Auth::user();

        if (!$user->organisations()->where('id', $organisation->id)->exists()) {
            return back()->with('error', "Accès refusé.");
        }

        setPermissionsTeamId($organisation->id);

        session([
            'active_organisation' => $organisation
        ]);

        $user->unsetRelation('roles')->unsetRelation('permissions');

        return to_route('organisations.index')->with('success', 'Organisation activée');
    }

    public function deactivate()
    {
        $user = Auth::user();

        // Retirer l'organisation active
        setPermissionsTeamId(null);

        session()->forget('active_organisation');

        $user->unsetRelation('roles')->unsetRelation('permissions');

        return to_route('organisations.index')->with('success', 'Organisation désactivée');
    }


    public function create()
    {
        return Inertia::render('Organisations/Create');
    }





    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:organisations,nom',
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

    public function show(Organisation $organisation)
    {
        $organisation->load(['user', 'clients', 'projets']);

        return Inertia::render('Organisations/Show', [
            'organisation' => $organisation
        ]);
    }

    public function edit(Organisation $organisation)
    {
        return Inertia::render('Organisations/Edit', [
            'organisation' => $organisation
        ]);
    }

    public function update(Request $request, Organisation $organisation): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:organisations,nom,' . $organisation->id,
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
        if (!$organisation->canBeDeleted()) {
            return redirect()
                ->route('organisations.index')
                ->with('error', "Impossible de supprimer l'organisation : elle est encore liée à des utilisateurs ou des projets.");
        }

        $organisation->delete();

        return redirect()
            ->route('organisations.index')
            ->with('success', "Organisation supprimée avec succès.");
    }

}
