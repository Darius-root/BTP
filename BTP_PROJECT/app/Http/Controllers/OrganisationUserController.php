<?php

namespace App\Http\Controllers;

use App\Models\OrganisationUser;
use App\Models\User;
use App\Services\OrganisationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class OrganisationUserController extends Controller
{

    public function index()
    {
        $organisationId = getPermissionsTeamId();
        $users = OrganisationUser::with(['user', 'role.permissions'])
            ->where('organisation_id', $organisationId)
            ->get()
            ->map(function ($ou) {
                $ou->user->unsetRelation('roles')->unsetRelation('permissions'); // reset Spatie relations
                return [
                    'id' => $ou->user->id,
                    'name' => $ou->user->name ?? $ou->user->email,
                    'email' => $ou->user->email,
                    'role' => [
                        'id' => $ou->role->id,
                        'name' => $ou->role->name,
                        'permissions' => $ou->role->permissions->map(fn($p) => [
                            'id' => $p->id,
                            'name' => $p->name,
                        ]),
                    ],
                    'created_at' => $ou->created_at->format('Y-m-d H:i'),
                ];
            });


        return Inertia::render('Organisations/Users/Index', [
            'orgUsers' => $users,
        ]);
    }

    // ===========================
    // SUPPRIMER UN UTILISATEUR DE L'ORGANISATION
    // ===========================



    public function destroy(User $user)
    {
        $organisationId = getPermissionsTeamId();

        if (! $organisationId) {
            return back()->with('error', "Aucune organisation active.");
        }

        if ($user->id === Auth::id()) {
            return back()->with('error', "Vous ne pouvez pas vous retirer vous-même de l’organisation.");
        }

        // 🔍récupérer la relation organisation_user
        $organisationUser = OrganisationUser::where('user_id', $user->id)
            ->where('organisation_id', $organisationId)
            ->with('role')
            ->first();

        if (! $organisationUser) {
            return back()->with('error', "Cet utilisateur n’appartient pas à cette organisation.");
        }



        $organisationUser->delete();

        return back()->with('success', "Utilisateur retiré de l’organisation avec succès.");
    }




    public function create()
    {

        if (OrganisationContext::hasPermission(Auth::user(), getPermissionsTeamId(), 'ORG_ADD_USER_TO_ORGANISATION') === false) {
            return back()->with('error', "Vous n'avez pas la permission d'ajouter un utilisateur à cette organisation.");
        }

        $organisationId = getPermissionsTeamId();
        $roles = Role::query()
            ->where('name', 'like', 'ORG_%')
            ->where(function ($query) use ($organisationId) {
                $query->whereNull('organisation_id')            // rôle système
                    ->orWhere('organisation_id', $organisationId); // rôle de l'orga active
            })
            ->orderBy('name')
            ->get()
            ->map(function ($role) use ($organisationId) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                ];
            });
        return Inertia::render('Organisations/Users/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
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

        return redirect()
            ->route('organisations.users.index')
            ->with('success', "Utilisateur ajouté à l’organisation avec succès.");
    }
}
