<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\OrganisationUser;
use App\Models\User;
use App\Services\OrganisationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Illuminate\Http\RedirectResponse;
use Throwable;

class OrganisationUserController extends Controller
{



    public function index()
    {
        $user = Auth::user();
        $organisationId = getPermissionsTeamId();

        if ($organisationId === null) {
            abort(403, 'Aucune organisation active.');
        }

        // Fixer le contexte Spatie (TEAM)
        setPermissionsTeamId($organisationId);

        // Permission de lecture des utilisateurs
        if (!$user->can('ORG_ORGANISATION_USER_VIEW')) {
            abort(403, 'Vous ne disposez pas des autorisations nécessaires pour consulter les utilisateurs.');
        }

        $authUserId = $user->id;

        $organisationUsers = OrganisationUser::with([
            'user.roles.permissions',
        ])
            ->where('organisation_id', $organisationId)
            ->where('user_id', '!=', $authUserId)
            ->get();

        $users = $organisationUsers->map(function ($ou) {
            $roles = $ou->user->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions->map(fn($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                    ]),
                ];
            });

            return [
                'id' => $ou->user->id,
                'name' => $ou->user->name ?? $ou->user->email,
                'email' => $ou->user->email,
                'roles' => $roles,
                'created_at' => $ou->created_at->format('Y-m-d H:i'),
            ];
        });

        return Inertia::render('Organisations/Users/Index', [
            'orgUsers' => $users,
        ]);
    }






    public function create()
    {

        if (OrganisationContext::hasPermission(Auth::user(), getPermissionsTeamId(), 'ORG_ORGANISATION_USER_CREATE') === false) {
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
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $organisationId = getPermissionsTeamId();

        if (!$organisationId) {
            return back()->with('error', "Aucune organisation active.");
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', "Utilisateur introuvable. Veuillez l’inviter.");
        }

        $exists = OrganisationUser::where('user_id', $user->id)
            ->where('organisation_id', $organisationId)
            ->exists();

        if ($exists) {
            return back()->with('error', "L’utilisateur est déjà membre de cette organisation.");
        }

        // Créer le lien organisation <-> user
        OrganisationUser::create([
            'user_id' => $user->id,
            'organisation_id' => $organisationId,
        ]);

        // Assigner plusieurs rôles dans le contexte de l’organisation
        foreach ($request->roles as $roleId) {
            $role = Role::find($roleId);
            if ($role) {
                // Spatie gère le team context via getPermissionsTeamId()
                $user->assignRole($role);
            }
        }

        return redirect()
            ->route('organisations.users.index')
            ->with('success', "Utilisateur ajouté à l’organisation avec succès.");
    }




    public function edit($userId)
    {
        $organisationId = getPermissionsTeamId();

        if (!$organisationId) {
            return back()->with('error', "Aucune organisation active.");
        }

        // Récupérer la relation OrganisationUser avec l'utilisateur
        $organisationUser = OrganisationUser::where('user_id', $userId)
            ->where('organisation_id', $organisationId)
            ->with('user')
            ->first();

        if (!$organisationUser) {
            return back()->with('error', "Cet utilisateur n’appartient pas à cette organisation.");
        }

        // Empêcher la modification de soi-même
        if ($organisationUser->user->id === Auth::id()) {
            return back()->with('error', "Vous ne pouvez pas modifier assigner  de rôle.");
        }

        //  Fixer le contexte Spatie
        setPermissionsTeamId($organisationId);

        //  Récupérer les rôles actuels de l’utilisateur dans ce team
        $organisationUser->user->unsetRelation('roles')->unsetRelation('permissions');
        $currentRoles = $organisationUser->user->roles->map(fn($role) => [
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissions->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
            ]),
        ]);

        // Rôles disponibles pour l'organisation
        $roles = Role::query()
            ->where('name', 'like', 'ORG_%')
            ->where(function ($query) use ($organisationId) {
                $query->whereNull('organisation_id') // rôles système
                    ->orWhere('organisation_id', $organisationId);
            })
            ->orderBy('name')
            ->get()
            ->map(fn($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'readonly' => is_null($role->organisation_id),
            ]);

        return Inertia::render('Organisations/Users/Edit', [
            'user' => [
                'id' => $organisationUser->user->id,
                'name' => $organisationUser->user->name ?? $organisationUser->user->email,
                'email' => $organisationUser->user->email,
                'roles' => $currentRoles, // tous les rôles actuels avec permissions
            ],
            'roles' => $roles, //  liste des rôles disponibles
        ]);
    }



    public function update(Request $request, User $user)
    {
        $organisationId = getPermissionsTeamId();

        if (!$organisationId) {
            return back()->with('error', "Aucune organisation active.");
        }

        $organisation = Organisation::find($organisationId);
        if ($user->id === $organisation->user_id || $user->id === Auth::id()) {
            return back()->with('error', "Vous ne pouvez pas modifier votre propre rôle.");
        }

        // Vérifier que l'utilisateur appartient bien à l'organisation
        $organisationUser = OrganisationUser::where('user_id', $user->id)
            ->where('organisation_id', $organisationId)
            ->first();

        if (!$organisationUser) {
            return back()->with('error', "Cet utilisateur n’appartient pas à cette organisation.");
        }

        //  Validation
        $data = $request->validate([
            'roles' => ['required', 'array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')],
        ]);

        //  Fixer le contexte Spatie
        setPermissionsTeamId($organisationId);

        //  Mettre à jour les rôles de l’utilisateur dans ce team
        $user->syncRoles($data['roles']);

        return redirect()
            ->route('organisations.users.index')
            ->with('success', "Rôles de l’utilisateur mis à jour avec succès.");
    }





    // ===========================
    // SUPPRIMER UN UTILISATEUR DE L'ORGANISATION
    // ===========================


    public function destroy(User $user)
    {
        $organisationId = getPermissionsTeamId();

        if (!$organisationId) {
            return back()->with('error', "Aucune organisation active.");
        }
        $organisation = Organisation::find($organisationId);
        if ($user->id === $organisation->user_id || $user->id === Auth::id()) {
            return back()->with('error', "Vous ne pouvez  pas le supprimer");
        }

        // 🔹 Vérifier que l'utilisateur appartient bien à l'organisation
        $organisationUser = OrganisationUser::where('user_id', $user->id)
            ->where('organisation_id', $organisationId)
            ->first();

        if (!$organisationUser) {
            return back()->with('error', "Cet utilisateur n’appartient pas à cette organisation.");
        }

        // Fixer le contexte Spatie
        setPermissionsTeamId($organisationId);

        // 🔹 Supprimer les rôles de l’utilisateur dans ce team
        $user->syncRoles([]);

        // 🔹 Supprimer toutes les permissions directes de l’utilisateur dans ce team
        $user->syncPermissions([]);

        // 🔹 Supprimer le lien organisation <-> user
        $organisationUser->delete();

        return back()->with('success', "Utilisateur retiré de l’organisation avec succès.");
    }



}
