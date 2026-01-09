<?php

namespace App\Http\Controllers;

use App\Models\OrganisationUser;
use App\Models\User;
use App\Services\OrganisationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Illuminate\Http\RedirectResponse;
use Throwable;

class OrganisationUserController extends Controller
{
    /**
     * Liste des utilisateurs de l'organisation
     */
    public function index()
    {
        try {
            $organisationId = getPermissionsTeamId();

            if (!$organisationId) {
                return back()->with('error', "Aucune organisation active.");
            }

            if (!OrganisationContext::hasPermission(Auth::user(), $organisationId, 'ORG_ORGANISATIONUSER_VIEW')) {
                return back()->with('error', "Vous n'avez pas la permission de consulter les utilisateurs de l'organisation.");
            }

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

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Formulaire d'ajout d'un utilisateur
     */
    public function create()
    {
        try {
            $organisationId = getPermissionsTeamId();

            if (!$organisationId) {
                return back()->with('error', "Aucune organisation active.");
            }

            if (!OrganisationContext::hasPermission(Auth::user(), $organisationId, 'ORG_ORGANISATIONUSER_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission d'ajouter un utilisateur à cette organisation.");
            }

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

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Ajouter un utilisateur à l'organisation
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $organisationId = getPermissionsTeamId();

            if (!$organisationId) {
                return back()->with('error', "Aucune organisation active.");
            }

            if (!OrganisationContext::hasPermission(Auth::user(), $organisationId, 'ORG_ORGANISATIONUSER_CREATE')) {
                return back()->with('error', "Vous n'avez pas la permission d'ajouter un utilisateur à cette organisation.");
            }

            $request->validate([
                'email' => ['required', 'email'],
                'role' => ['required', 'exists:roles,name'],
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return back()->with('error', "Utilisateur introuvable. Veuillez l'inviter.");
            }

            $role = Role::where('name', $request->role)->first();

            $exists = OrganisationUser::where('user_id', $user->id)
                ->where('organisation_id', $organisationId)
                ->exists();

            if ($exists) {
                return back()->with('error', "L'utilisateur est déjà membre de cette organisation.");
            }

            OrganisationUser::create([
                'user_id' => $user->id,
                'organisation_id' => $organisationId,
                'role_id' => $role->id,
            ]);

            return redirect()
                ->route('organisations.users.index')
                ->with('success', "Utilisateur ajouté à l'organisation avec succès.");

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Formulaire d'édition d'un utilisateur
     */
    public function edit($userId)
    {
        try {
            $organisationId = getPermissionsTeamId();

            if (!$organisationId) {
                return back()->with('error', "Aucune organisation active.");
            }

            if (!OrganisationContext::hasPermission(Auth::user(), $organisationId, 'ORG_ORGANISATIONUSER_EDIT')) {
                return back()->with('error', "Vous n'avez pas la permission de modifier les utilisateurs de cette organisation.");
            }

            $organisationUser = OrganisationUser::where('user_id', $userId)
                ->where('organisation_id', $organisationId)
                ->with('role', 'user')
                ->first();

            if (!$organisationUser) {
                return back()->with('error', "Cet utilisateur n'appartient pas à cette organisation.");
            }

            if ($organisationUser->user->id === Auth::id()) {
                return back()->with('error', "Vous ne pouvez pas modifier votre propre rôle.");
            }

            $roles = Role::query()
                ->where('name', 'like', 'ORG_%')
                ->where(function ($query) use ($organisationId) {
                    $query->whereNull('organisation_id')
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
                ],
                'currentRoleId' => $organisationUser->role_id,
                'roles' => $roles,
            ]);

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Mettre à jour le rôle d'un utilisateur
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        try {
            $organisationId = getPermissionsTeamId();

            if (!$organisationId) {
                return back()->with('error', "Aucune organisation active.");
            }

            if (!OrganisationContext::hasPermission(Auth::user(), $organisationId, 'ORG_ORGANISATIONUSER_EDIT')) {
                return back()->with('error', "Vous n'avez pas la permission de modifier les utilisateurs de cette organisation.");
            }

            if ($user->id === Auth::id()) {
                return back()->with('error', "Vous ne pouvez pas modifier votre propre rôle.");
            }

            $validated = $request->validate([
                'role_id' => ['required', 'exists:roles,id'],
            ]);

            $organisationUser = OrganisationUser::where('user_id', $user->id)
                ->where('organisation_id', $organisationId)
                ->with('role')
                ->first();

            if (!$organisationUser) {
                return back()->with('error', "Cet utilisateur n'appartient pas à cette organisation.");
            }

            $organisationUser->update([
                'role_id' => $validated['role_id'],
            ]);

            return redirect()
                ->route('organisations.users.index')
                ->with('success', "Rôle de l'utilisateur mis à jour avec succès.");

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Supprimer un utilisateur de l'organisation
     */
    public function destroy(User $user): RedirectResponse
    {
        try {
            $organisationId = getPermissionsTeamId();

            if (!$organisationId) {
                return back()->with('error', "Aucune organisation active.");
            }

            if (!OrganisationContext::hasPermission(Auth::user(), $organisationId, 'ORG_ORGANISATIONUSER_DELETE')) {
                return back()->with('error', "Vous n'avez pas la permission de supprimer des utilisateurs de cette organisation.");
            }

            if ($user->id === Auth::id()) {
                return back()->with('error', "Vous ne pouvez pas vous retirer vous-même de l'organisation.");
            }

            $organisationUser = OrganisationUser::where('user_id', $user->id)
                ->where('organisation_id', $organisationId)
                ->with('role')
                ->first();

            if (!$organisationUser) {
                return back()->with('error', "Cet utilisateur n'appartient pas à cette organisation.");
            }

            $organisationUser->delete();

            return back()->with('success', "Utilisateur retiré de l'organisation avec succès.");

        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}