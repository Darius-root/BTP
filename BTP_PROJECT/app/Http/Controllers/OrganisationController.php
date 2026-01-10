<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\OrganisationUser;
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
            $organisations = Organisation::where('is_system', false)
                ->with(['clients', 'projets', 'user'])
                ->get();
            return Inertia::render('Organisations/Index', [
                'organisations' => $organisations,
            ]);
        }

        // ===== USER NORMAL =====
        // Récupérer toutes les organisations normales où l'utilisateur est membre
        $organisationUsers = OrganisationUser::with(['organisation'])
            ->where('user_id', $user->id)
            ->whereHas('organisation', fn($q) => $q->where('is_system', false))
            ->get();

        $organisationsWithRoles = $organisationUsers->map(function ($ou) use ($currentTeamId, $user) {
            // Changer le contexte Spatie pour cette organisation
            setPermissionsTeamId($ou->organisation->id);

            // Reset relations pour éviter les conflits
            $user->unsetRelation('roles')->unsetRelation('permissions');

            return [
                'team' => $ou->organisation,
                // Récupérer tous les rôles Spatie dans ce team
                'roles' => $user->getRoleNames()->toArray(),
                'statut' => $ou->organisation->id === $currentTeamId,
            ];
        })->all();

        // Revenir au contexte initial
        setPermissionsTeamId($currentTeamId);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        return Inertia::render('Organisations/Index', [
            'user' => $user,
            'teamsWithRoles' => $organisationsWithRoles,
        ]);
    }

    /* ==========================================================
     | ACTIVATE / DEACTIVATE
     ========================================================== */
    public function activate(Organisation $organisation)
    {
        $user = Auth::user();
        $currentTeamId = getPermissionsTeamId();

        // ===== Vérifier appartenance =====
        $exists = OrganisationUser::where('organisation_id', $organisation->id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$exists) {
            return back()->with('error', 'Accès refusé.');
        }

        setPermissionsTeamId($organisation->id);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        if (!$user->can('ORG_ORGANISATION_ACTIVATE')) {
            setPermissionsTeamId($currentTeamId);
            $user->unsetRelation('roles')->unsetRelation('permissions');

            return back()->with('error', 'Permission refusée.');
        }

        session([
            'active_organisation_id' => $organisation->id,
            'active_organisation_name' => $organisation->nom,
        ]);

        return redirect()
            ->route('organisations.index')
            ->with('success', 'Organisation activée');
    }

    public function deactivate()
    {
        $user = Auth::user();
        $currentTeamId = getPermissionsTeamId();

        if ($currentTeamId === null) {
            return back()->with('error', 'Aucune organisation active.');
        }

        // ===== Contexte organisation =====
        setPermissionsTeamId($currentTeamId);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        // ===== Permission =====
        if (!$user->can('ORG_ORGANISATION_DEACTIVATE')) {
            return back()->with('error', 'Permission refusée.');
        }

        // ===== Désactivation =====
        setPermissionsTeamId(null);
        session()->forget([
            'active_organisation_id',
            'active_organisation_name'
        ]);

        $user->unsetRelation('roles')->unsetRelation('permissions');

        return back()->with('success', 'Organisation désactivée');
    }


    /* ==========================================================
     | CREATE / STORE
     ========================================================== */
    public function create()
    {
        $user = Auth::user();
        $currentTeamId = getPermissionsTeamId();

        // Vérifier permission si dans un contexte organisation
        if ($currentTeamId !== null) {
            setPermissionsTeamId($currentTeamId);
            $user->unsetRelation('roles')->unsetRelation('permissions');

            if (!$user->can('ORG_ORGANISATION_CREATE')) {
                return back()->with('error', "Vous n'avez pas les permissions pour créer une organisation");
            }
        }

        return Inertia::render('Organisations/Create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $currentTeamId = getPermissionsTeamId();
        // Vérifier permission si dans un contexte organisation
        if ($currentTeamId !== null) {
            setPermissionsTeamId($currentTeamId);
            $user->unsetRelation('roles')->unsetRelation('permissions');

            if (!$user->can('ORG_ORGANISATION_CREATE')) {
                return back()->with('error', "Permission refusée.");
            }
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

        $validated['user_id'] = $user->id;

        try {
            DB::transaction(function () use ($validated, $user, $currentTeamId) {
                // Créer l'organisation
                $organisation = Organisation::create($validated);

                // Créer la relation dans organisation_users
                OrganisationUser::create([
                    'user_id' => $user->id,
                    'organisation_id' => $organisation->id,
                ]);

                // Changer le contexte vers la nouvelle organisation
                setPermissionsTeamId($organisation->id);
                $user->unsetRelation('roles')->unsetRelation('permissions');

                // Assigner le rôle ORG_ADMIN
                $user->assignRole('ORG_ADMIN');

                // Revenir au contexte initial
                setPermissionsTeamId($currentTeamId);
                $user->unsetRelation('roles')->unsetRelation('permissions');
            });

            return redirect()->route('organisations.index')
                ->with('success', 'Organisation créée avec succès.');
        } catch (Throwable $e) {
            return back()->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /* ==========================================================
     | SHOW / EDIT / UPDATE
     ========================================================== */
    public function show(Organisation $organisation)
    {
        $user = Auth::user();
        $currentTeamId = getPermissionsTeamId();

        if ($currentTeamId === null || $currentTeamId !== $organisation->id) {
            return back()->with('error', "Accès refusé.");
        }

        setPermissionsTeamId($currentTeamId);
        $user->unsetRelation('roles')->unsetRelation('permissions');
        if (!$user->can('ORG_ORGANISATION_VIEW')) {
            return back()->with('error', "Permission refusée.");
        }

        return Inertia::render('Organisations/Show', [
            'organisation' => $organisation->load(['user', 'clients', 'projets']),
        ]);
    }

    public function edit(Organisation $organisation)
    {
        $user = Auth::user();
        $currentTeamId = getPermissionsTeamId();

        if ($currentTeamId === null || $currentTeamId !== $organisation->id) {
            return back()->with('error', "Accès refusé.");
        }

        setPermissionsTeamId($currentTeamId);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        if (!$user->can('ORG_ORGANISATION_EDIT')) {
            return back()->with('error', "Permission refusée.");
        }

        return Inertia::render('Organisations/Edit', [
            'organisation' => $organisation,
        ]);
    }

    public function update(Request $request, Organisation $organisation)
    {
        $user = Auth::user();
        $currentTeamId = getPermissionsTeamId();

        if ($currentTeamId === null || $currentTeamId !== $organisation->id) {
            return back()->with('error', "Accès refusé.");
        }

        setPermissionsTeamId($currentTeamId);
        $user->unsetRelation('roles')->unsetRelation('permissions');

        if (!$user->can('ORG_ORGANISATION_EDIT')) {
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
    public function destroy(Organisation $organisation)
    {
        $user = Auth::user();
        $currentTeamId = getPermissionsTeamId();

        // Vérifier que c'est le créateur
        if ((int) $organisation->created_by !== (int) $user->id) {
            return back()->with(
                'error',
                "Seul le propriétaire de l'organisation peut la supprimer."
            );
        }

        // Vérifier la permission
        if ($currentTeamId !== null) {
            setPermissionsTeamId($currentTeamId);
            $user->unsetRelation('roles')->unsetRelation('permissions');

            if (!$user->can('ORG_ORGANISATION_DELETE')) {
                return back()->with('error', "Permission refusée.");
            }
        }

        // Vérifier si l'organisation peut être supprimée
        if (!$organisation->canBeDeleted()) {
            return back()->with(
                'error',
                "Suppression impossible : d'autres utilisateurs sont liés à cette organisation."
            );
        }

        try {
            DB::transaction(function () use ($organisation) {
                // Supprimer les relations organisation_users
                $organisation->organisationUsers()->delete();

                // Supprimer l'organisation
                $organisation->delete();
            });

            return redirect()
                ->route('organisations.index')
                ->with('success', "Organisation supprimée avec succès.");
        } catch (Throwable $e) {
            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }
}
