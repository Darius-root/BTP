<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    /**
     * Affiche tous les rôles et permissions
     */
    public function index()
    {
        // Récupérer tous les rôles avec leurs permissions
        $roles = Role::with('permissions')->get();

        // Séparer les rôles par nomenclature
        $systemRoles = $roles->filter(function ($role) {
            return str_starts_with($role->name, 'SYSTEM_');
        });

        $orgRoles = $roles->filter(function ($role) {
            return str_starts_with($role->name, 'ORG_');
        });

        // Récupérer toutes les permissions
        $permissions = Permission::all();


        return inertia('Roles/Index', [
            'systemRoles' => $systemRoles,
            'orgRoles' => $orgRoles,

        ]);
    }

    public function create()
    {
        $permissions = Permission::all();

        $systemPermissions = $permissions->filter(function ($perm) {
            return str_starts_with($perm->name, 'SYSTEM_');
        });

        $orgPermissions = $permissions->filter(function ($perm) {
            return str_starts_with($perm->name, 'ORG_');
        });
        return Inertia::render('Roles/Create', [
            'systemPermissions' => $systemPermissions,
            'orgPermissions' => $orgPermissions,
        ]);
    }
    public function show(Role $role)
    {
        // Charger les permissions liées au rôle
        $role->load('permissions');

        return inertia('Roles/Show', [
            'role' => $role,
        ]);
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        //  Nom du rôle en MAJUSCULE
        $roleName = strtoupper($validated['name']);

        //  Détermination du type via le préfixe
        if (str_starts_with($roleName, 'SYSTEM_')) {
            $expectedPrefix = 'SYSTEM_';
        } elseif (str_starts_with($roleName, 'ORG_')) {
            $expectedPrefix = 'ORG_';
        } else {
            throw ValidationException::withMessages([
                'name' => 'Le nom du rôle doit commencer par SYSTEM_ ou ORG_.',
            ]);
        }

        // Récupération des permissions sélectionnées
        $permissions = Permission::whereIn('id', $validated['permissions'])->get();

        //  Vérification stricte des permissions
        $invalidPermissions = $permissions->filter(
            fn($permission) => !str_starts_with($permission->name, $expectedPrefix)
        );

        if ($invalidPermissions->isNotEmpty()) {
            throw ValidationException::withMessages([
                'permissions' => 'Certaines permissions ne correspondent pas au type du rôle.',
            ]);
        }

        DB::transaction(function () use ($roleName, $permissions) {
            $role = Role::create([
                'name' => $roleName,
            ]);

            $role->permissions()->sync($permissions->pluck('id')->toArray());
        });

        return redirect()
            ->route('roles.index')
            ->with('success', 'Rôle créé avec succès.');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->exists()) {

            return redirect()
                ->back()
                ->with('error', 'Ce rôle est attribué à des utilisateurs et ne peut pas être supprimé.');
        }

        // $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Rôle supprimé avec succès.');
    }

    public function edit(Role $role)
    {
        // Charger toutes les permissions
        $permissions = Permission::all();

        // Charger les permissions déjà associées au rôle (IDs uniquement)
        $rolePermissions = $role->permissions()->pluck('id')->toArray();


        // Filtrer les permissions par nomenclature
        $systemPermissions = $permissions->filter(fn($p) => str_starts_with($p->name, 'SYSTEM_'));
        $orgPermissions    = $permissions->filter(fn($p) => str_starts_with($p->name, 'ORG_'));

        // Déterminer la catégorie du rôle courant
        $category = str_starts_with($role->name, 'SYSTEM_') ? 'system' : 'org';

        // Envoyer uniquement les permissions de la catégorie du rôle
        $filteredPermissions = $category === 'system' ? $systemPermissions : $orgPermissions;
        return Inertia::render('Roles/Edit', [
            'role' => [
                'id'          => $role->id,
                'name'        => $role->name,
                'permissions' => $rolePermissions,
                'category'    => $category,
            ],
            'permissions' => $filteredPermissions->values(), // uniquement celles de la catégorie
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        // Validation
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'permissions' => ['array'], // tableau d'IDs
            'permissions.*' => ['exists:permissions,id'],
        ]);

        // Mise à jour du nom
        $role->update([
            'name' => $validated['name'],
        ]);

        // Synchronisation des permissions
        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        // Flash message
        return redirect()
            ->route('roles.index')
            ->with('success', 'Rôle mis à jour avec succès.');
    }
}
