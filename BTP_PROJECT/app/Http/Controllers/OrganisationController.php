<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\OrganisationUser;
use App\Models\User;
use App\Services\OrganisationService;
use Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

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
                'team'   => $organisation,
                'roles'  => $user->getRoleNames(),
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

        if (! $user->organisations()->where('id', $organisation->id)->exists()) {
            return back()->with('error', "Accès refusé.");
        }

        setPermissionsTeamId($organisation->id);

        session([
            'active_organisation' => $organisation
        ]);

        $user->unsetRelation('roles')->unsetRelation('permissions');

        return back()->with('success', 'Organisation activée');
    }

    public function deactivate()
    {
        $user = Auth::user();

        // Retirer l'organisation active
        setPermissionsTeamId(null);

        session()->forget('active_organisation');

        $user->unsetRelation('roles')->unsetRelation('permissions');

        return back()->with('success', 'Organisation désactivée');
    }

    public function addUserToOrganisation()
    {
        $user = Auth::user();
        if (! $user->can('checkPermission', [getPermissionsTeamId(), 'ADD_USER_TO_ORGANISATION'])) {

            return redirect()
                ->back()
                ->with([
                    'error' => "Vous n'avez pas la permission d'ajouter un utilisateur à cette organisation."
                ]);
        }

        $roles = Role::where('name', '!=', 'SYSTEM_ADMIN_PLATEFORME');
        return Inertia::render('Organisations/AddUser', [
            'organisations' => $user->organisations,
            'roles' => $roles->get(),
        ]);
    }



    public function storeUserToOrganisation(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'organisation_id' => 'required|exists:organisations,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $organisation = Organisation::findOrFail($request->organisation_id);

        // Role par défaut : "view organisation"
        $defaultRole = Role::where('name', 'view organisation')->first();

        if (!$defaultRole) {
            return back()->with('error', 'Le rôle par défaut "view organisation" n’existe pas.');
        }

        // Vérifier que l’utilisateur n’est pas déjà dans cette organisation
        $exists = OrganisationUser::where('user_id', $user->id)
            ->where('organisation_id', $organisation->id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'L’utilisateur est déjà membre de cette organisation.');
        }

        // Ajouter l’utilisateur avec le rôle par défaut
        OrganisationUser::create([
            'user_id' => $user->id,
            'organisation_id' => $organisation->id,
            'role_id' => $defaultRole->id,
        ]);

        return back()->with('success', "Utilisateur ajouté à l'organisation avec le rôle par défaut.");
    }
}
