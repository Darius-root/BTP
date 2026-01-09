<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Services\OrganisationContext;
use App\Services\OrganisationService;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Throwable;

class OrganisationController extends Controller
{
    /* ==========================================================
     | INDEX
     ========================================================== */
    public function index(OrganisationService $organisationService)
    {
        try {
            $user = Auth::user();
            $currentTeamId = getPermissionsTeamId();

            /** -------- SYSTEM ADMIN CHECK -------- */
            setPermissionsTeamId($organisationService->system());
            $user->unsetRelation('roles')->unsetRelation('permissions');

            $isSystemAdmin = $user->hasRole('SYSTEM_ADMIN_PLATEFORME');

            setPermissionsTeamId($currentTeamId);
            $user->unsetRelation('roles')->unsetRelation('permissions');

            /** -------- SYSTEM ADMIN -------- */
            if ($isSystemAdmin) {
                return Inertia::render('Organisations/Index', [
                    'organisations' => Organisation::where('is_system', false)->get(),
                ]);
            }

            /** -------- USER NORMAL -------- */
            $organisations = $user->organisations()
                ->where('is_system', false)
                ->get();

            if ($organisations->isEmpty()) {
                return back()->with('error', "Vous n'avez accès à aucune organisation.");
            }

            $teamsWithRoles = [];

            foreach ($organisations as $organisation) {
                setPermissionsTeamId($organisation->id);
                $user->unsetRelation('roles')->unsetRelation('permissions');

                $teamsWithRoles[] = [
                    'team' => $organisation,
                    'roles' => $user->getRoleNames(),
                    'statut' => $organisation->id === $currentTeamId,
                ];
            }

            setPermissionsTeamId($currentTeamId);
            $user->unsetRelation('roles')->unsetRelation('permissions');

            return Inertia::render('Organisations/Index', [
                'teamsWithRoles' => $teamsWithRoles,
            ]);

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /* ==========================================================
     | ACTIVATE / DEACTIVATE
     ========================================================== */
    public function activate(Organisation $organisation): RedirectResponse
    {
        try {
            $user = Auth::user();

            if (!$user->organisations()->where('organisations.id', $organisation->id)->exists()) {
                return back()->with('error', "Accès refusé.");
            }

            setPermissionsTeamId($organisation->id);
            session(['active_organisation' => $organisation]);

            $user->unsetRelation('roles')->unsetRelation('permissions');

            return to_route('organisations.index')
                ->with('success', 'Organisation activée.');

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function deactivate(): RedirectResponse
    {
        try {
            setPermissionsTeamId(null);
            session()->forget('active_organisation');

            Auth::user()->unsetRelation('roles')->unsetRelation('permissions');

            return to_route('organisations.index')
                ->with('success', 'Organisation désactivée.');

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /* ==========================================================
     | CREATE / STORE
     ========================================================== */
    public function create()
    {
        $currentTeamId = getPermissionsTeamId();

        if (
            $currentTeamId !== null &&
            !OrganisationContext::hasPermission(Auth::user(), $currentTeamId, 'ORG_ORGANISATION_CREATE')
        ) {
            return back()->with('error', "Permission refusée.");
        }

        return Inertia::render('Organisations/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $currentTeamId = getPermissionsTeamId();
            $user = Auth::user();

            if (
                $currentTeamId !== null &&
                !OrganisationContext::hasPermission($user, $currentTeamId, 'ORG_ORGANISATION_CREATE')
            ) {
                return back()->with('error', "Permission refusée.");
            }

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

            $validated['created_by'] = $user->id;

            DB::transaction(function () use ($validated, $user) {
                $organisation = Organisation::create($validated);
                $user->organisations()->attach($organisation->id);
                setPermissionsTeamId($organisation->id);
                session(['active_organisation' => $organisation]);
            });

            return redirect()
                ->route('organisations.index')
                ->with('success', 'Organisation créée avec succès.');

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /* ==========================================================
     | SHOW / EDIT / UPDATE
     ========================================================== */
    public function show(Organisation $organisation)
    {
        if (!OrganisationContext::hasPermission(Auth::user(), getPermissionsTeamId(), 'ORG_ORGANISATION_VIEW')) {
            return back()->with('error', "Permission refusée.");
        }

        return Inertia::render('Organisations/Show', [
            'organisation' => $organisation->load(['user', 'clients', 'projets']),
        ]);
    }

    public function edit(Organisation $organisation)
    {
        if (!OrganisationContext::hasPermission(Auth::user(), getPermissionsTeamId(), 'ORG_ORGANISATION_EDIT')) {
            return back()->with('error', "Permission refusée.");
        }

        return Inertia::render('Organisations/Edit', [
            'organisation' => $organisation,
        ]);
    }

    public function update(Request $request, Organisation $organisation): RedirectResponse
    {
        if (!OrganisationContext::hasPermission(Auth::user(), getPermissionsTeamId(), 'ORG_ORGANISATION_EDIT')) {
            return back()->with('error', "Permission refusée.");
        }

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

        return redirect()
            ->route('organisations.index')
            ->with('success', 'Organisation mise à jour avec succès.');
    }

    /* ==========================================================
     | DESTROY
     ========================================================== */
    public function destroy(Organisation $organisation): RedirectResponse
    {
        $user = Auth::user();

        if ((int) $organisation->created_by !== (int) $user->id) {
            return back()->with(
                'error',
                "Seul le propriétaire de l'organisation peut la supprimer."
            );
        }


        if (
            !OrganisationContext::hasPermission(
                $user,
                getPermissionsTeamId(),
                'ORG_ORGANISATION_DELETE'
            )
        ) {
            return back()->with('error', "Permission refusée.");
        }

        if (!$organisation->canBeDeleted()) {
            return back()->with(
                'error',
                "Suppression impossible : d'autres utilisateurs sont liés à cette organisation."
            );
        }

        DB::transaction(function () use ($organisation) {

            $organisation->organisationUsers()
                ->where('user_id', $organisation->created_by)
                ->delete();

            $organisation->delete();
        });

        return redirect()
            ->route('organisations.index')
            ->with('success', "Organisation supprimée avec succès.");
    }

}
