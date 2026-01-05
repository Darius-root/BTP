<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\OrganisationUser;
use App\Models\User;
use App\Services\OrganisationContext;
use App\Services\OrganisationService;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
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



   


 
}
