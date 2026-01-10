<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
        // Vérifier la permission SYSTEM_ROLE_VIEW
        if (!Auth::user()->can('SYSTEM_ROLE_VIEW')) {
            abort(403, 'Vous n\'avez pas la permission de consulter les rôles.');
        }

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
            'permissions' => $permissions,
        ]);
    }

    public function create()
    {
        // Vérifier la permission SYSTEM_ROLE_CREATE
        if (!Auth::user()->can('SYSTEM_ROLE_CREATE')) {
            abort(403, 'Vous n\'avez pas la permission de créer des rôles.');
        }

        $permissions = Permission::all();
        $systemPermissions = Permission::where('name', 'like', 'SYSTEM_%')
            ->select('id', 'name')
            ->get();

        $orgPermissions = Permission::where('name', 'like', 'ORG_%')
            ->select('id', 'name')
            ->get();

        $orgPermissions = $permissions->filter(function ($perm) {
            return str_starts_with($perm->name, 'ORG_');
        });

        return Inertia::render('Roles/Create', [
            'systemPermissions' => $systemPermissions,
            'orgPermissions'    => $orgPermissions,
        ]);
    }

    public function show(Role $role)
    {
        // Vérifier la permission SYSTEM_ROLE_VIEW
        if (!Auth::user()->can('SYSTEM_ROLE_VIEW')) {
            abort(403, 'Vous n\'avez pas la permission de consulter les rôles.');
        }

        // Charger les permissions liées au rôle
        $role->load('permissions');

        return inertia('Roles/Show', [
            'role' => $role,
        ]);
    }

    public function store(Request $request)
    {
        // Vérifier la permission SYSTEM_ROLE_CREATE
        if (!Auth::user()->can('SYSTEM_ROLE_CREATE')) {
            abort(403, 'Vous n\'avez pas la permission de créer des rôles.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        // Nom du rôle en MAJUSCULE
        $roleName = strtoupper($validated['name']);

        // Détermination du type via le préfixe
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

        // Vérification stricte des permissions
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
                'organisation_id' => null,
            ]);

            $role->permissions()->sync($permissions->pluck('id')->toArray());
        });

        return redirect()
            ->route('roles.index')
            ->with('success', 'Rôle créé avec succès.');
    }

    public function destroy(Role $role)
    {
        // Vérifier la permission SYSTEM_ROLE_DELETE
        if (!Auth::user()->can('SYSTEM_ROLE_DELETE')) {
            abort(403, 'Vous n\'avez pas la permission de supprimer des rôles.');
        }

        // Empêcher la suppression des rôles système par défaut
        $protectedRoles = ['SYSTEM_ADMIN_PLATEFORME', 'SYSTEM_COLLECTEUR', 'ORG_ADMIN', 'ORG_COLLECTEUR'];
        
        if (in_array($role->name, $protectedRoles)) {
            return redirect()
                ->back()
                ->with('error', 'Ce rôle est protégé et ne peut pas être supprimé.');
        }

        if ($role->users()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'Ce rôle est attribué à des utilisateurs et ne peut pas être supprimé.');
        }

        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Rôle supprimé avec succès.');
    }

    public function edit(Role $role)
    {
        // Vérifier la permission SYSTEM_ROLE_EDIT
        if (!Auth::user()->can('SYSTEM_ROLE_EDIT')) {
            abort(403, 'Vous n\'avez pas la permission de modifier des rôles.');
        }

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
            'permissions' => $filteredPermissions->values(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        // Vérifier la permission SYSTEM_ROLE_EDIT
        if (!Auth::user()->can('SYSTEM_ROLE_EDIT')) {
            abort(403, 'Vous n\'avez pas la permission de modifier des rôles.');
        }

        // Empêcher la modification des rôles système par défaut (sauf permissions)
        $protectedRoles = ['SYSTEM_ADMIN_PLATEFORME', 'SYSTEM_COLLECTEUR', 'ORG_ADMIN', 'ORG_COLLECTEUR'];
        
        if (in_array($role->name, $protectedRoles) && $request->has('name') && $request->name !== $role->name) {
            return back()
                ->withErrors(['name' => 'Le nom de ce rôle protégé ne peut pas être modifié.'])
                ->withInput();
        }

        // Validation
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        // Vérification du préfixe
        $roleName = strtoupper($validated['name']);
        $currentPrefix = str_starts_with($role->name, 'SYSTEM_') ? 'SYSTEM_' : 'ORG_';
        $newPrefix = str_starts_with($roleName, 'SYSTEM_') ? 'SYSTEM_' : 'ORG_';

        if ($currentPrefix !== $newPrefix) {
            throw ValidationException::withMessages([
                'name' => 'Le préfixe du rôle ne peut pas être modifié (SYSTEM_/ORG_).',
            ]);
        }

        // Récupération et vérification des permissions
        $permissions = Permission::whereIn('id', $validated['permissions'])->get();
        $invalidPermissions = $permissions->filter(
            fn($permission) => !str_starts_with($permission->name, $currentPrefix)
        );

        if ($invalidPermissions->isNotEmpty()) {
            throw ValidationException::withMessages([
                'permissions' => 'Certaines permissions ne correspondent pas au type du rôle.',
            ]);
        }

        try {
            DB::transaction(function () use ($role, $roleName, $validated, $permissions) {
                // Mise à jour du nom uniquement si différent
                if ($role->name !== $roleName) {
                    $role->update([
                        'name' => $roleName,
                        'organisation_id' => null,
                    ]);
                }

                // Synchronisation des permissions
                $role->permissions()->sync($validated['permissions']);
            });

            return redirect()
                ->route('roles.index')
                ->with('success', 'Rôle mis à jour avec succès.');
                
        } catch (\Illuminate\Database\QueryException $e) {
            // Gestion d'erreur doublon
            if ($e->getCode() === '23000') {
                return back()
                    ->withErrors(['name' => 'Ce nom de rôle existe déjà.'])
                    ->withInput();
            }

            // Autres erreurs : relancer
            throw $e;
        } catch (\Exception $e) {
            return back()
                ->withErrors(['general' => 'Une erreur est survenue lors de la mise à jour du rôle.'])
                ->withInput();
        }
    }
}