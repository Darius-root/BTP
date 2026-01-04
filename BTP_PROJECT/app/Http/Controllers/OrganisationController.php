<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\OrganisationUser;
use App\Models\User;
use App\Services\OrganisationContext;
use App\Services\OrganisationService;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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



    public function addUserToOrganisation()
    {

        if (OrganisationContext::hasPermission(Auth::user(), getPermissionsTeamId(), 'ORG_ADD_USER_TO_ORGANISATION') === false) {
            return back()->with('error', "Vous n'avez pas la permission d'ajouter un utilisateur à cette organisation.");
        }

        $user = Auth::user();
        $organisationId = getPermissionsTeamId();

        if (! $organisationId) {
            return back()->with('error', "Aucune organisation active.");
        }



        $roles = Role::where('name', 'like', 'ORG_%')->get();

        return Inertia::render('Organisations/Users/AddUser', [
            'roles' => $roles,
        ]);
    }

    public function storeUserToOrganisation(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'role'  => ['required', 'exists:roles,name'],
        ]);

        $organisationId = getPermissionsTeamId();

        if (! $organisationId) {
            return back()->with('error', "Aucune organisation active.");
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->with('error', "Utilisateur introuvable. Veuillez l’inviter.");
        }

        $role = Role::where('name', $request->role)->first();

        $exists = OrganisationUser::where('user_id', $user->id)
            ->where('organisation_id', $organisationId)
            ->exists();

        if ($exists) {
            return back()->with('error', "L’utilisateur est déjà membre de cette organisation.");
        }

        OrganisationUser::create([
            'user_id'         => $user->id,
            'organisation_id' => $organisationId,
            'role_id'         => $role->id,
        ]);

        return back()->with('success', "Utilisateur ajouté à l’organisation avec succès.");
    }


    public function roleOrganisation()
    {
        $user = Auth::user();
        $user->unsetRelation('roles')->unsetRelation('permissions');
        
        $roles = $user->getRoleNames();

        dd($roles);
        return Inertia::render('Organisations/RoleOrganisation', [
            'roles' => $roles,
        ]);
    }

      public function storeRoleOrganisation()
    {

    }
}
