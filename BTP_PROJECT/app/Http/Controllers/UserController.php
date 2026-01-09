<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OrganisationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Liste des utilisateurs
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();

        // Vérifie la permission
        if (!$currentUser->can('SYSTEM_USER_VIEW')) {
            abort(403, "Vous n'avez pas la permission de voir les utilisateurs.");
        }

        $search = $request->input('search');
        $columns = $request->input('columns', []);
        $allowed = ['id', 'name', 'email', 'created_at'];

        $columns = array_intersect($columns, $allowed);

        $query = User::query();

        $users = $query
            ->when($search && count($columns), function ($q) use ($search, $columns) {
                $q->where(function ($sub) use ($search, $columns) {
                    foreach ($columns as $column) {
                        if ($column === 'created_at') {
                            $sub->orWhereDate('created_at', $search);
                        } else {
                            $sub->orWhere($column, 'like', "%{$search}%");
                        }
                    }
                });
            })
            ->paginate(5)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Voir un utilisateur et ses rôles dans chaque organisation
     */
    public function show(User $user, OrganisationService $organisationService)
    {
        $currentUser = Auth::user();

        if (!$currentUser->can('SYSTEM_USER_VIEW')) {
            abort(403, "Vous n'avez pas la permission de voir cet utilisateur.");
        }

        $organisationsWithRoles = [];

        $currentTeamId = getPermissionsTeamId();
        foreach ($user->organisations as $organisation) {
            setPermissionsTeamId($organisation->id);

            $user->unsetRelation('roles')->unsetRelation('permissions');

            $organisationsWithRoles[] = [
                'team' => $organisation,
                'roles' => $user->getRoleNames(),
            ];
        }

        $user->unsetRelation('roles')->unsetRelation('permissions');
        setPermissionsTeamId($currentTeamId);

        return Inertia::render('Users/Show', [
            'user' => $user,
            'teamsWithRoles' => $organisationsWithRoles,
        ]);
    }

    /**
     * Formulaire pour créer un utilisateur
     */
    public function create()
    {
        $currentUser = Auth::user();

        if (!$currentUser->can('SYSTEM_USER_CREATE')) {
            abort(403, "Vous n'avez pas la permission de créer un utilisateur.");
        }

        return Inertia::render('Users/Create');
    }

    /**
     * Sauvegarder un nouvel utilisateur
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();

        if (!$currentUser->can('SYSTEM_USER_CREATE')) {
            abort(403, "Vous n'avez pas la permission de créer un utilisateur.");
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        return redirect()->route('users.index')->with('success', 'Utilisateur créé avec succès.');
    }

    /**
     * Formulaire pour éditer un utilisateur
     */
    public function edit(User $user)
    {
        $currentUser = Auth::user();

        if (!$currentUser->can('SYSTEM_USER_EDIT')) {
            abort(403, "Vous n'avez pas la permission de modifier cet utilisateur.");
        }

        return Inertia::render('Users/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user();

        if (!$currentUser->can('SYSTEM_USER_EDIT')) {
            abort(403, "Vous n'avez pas la permission de modifier cet utilisateur.");
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$user->id}",
        ]);

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(User $user)
    {
        $currentUser = Auth::user();

        if (!$currentUser->can('SYSTEM_USER_DELETE')) {
            abort(403, "Vous n'avez pas la permission de supprimer cet utilisateur.");
        }

        if ($user->id === $currentUser->id) {
            abort(403, "Vous ne pouvez pas supprimer votre propre compte.");
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé avec succès.');
    }
}
