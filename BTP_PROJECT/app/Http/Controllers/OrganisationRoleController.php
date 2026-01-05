<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class OrganisationRoleController extends Controller
{




    public function index()
    {
        $organisationId = getPermissionsTeamId(); // org active

        $roles = Role::query()
            ->where('name', 'like', 'ORG_%')
            ->where(function ($query) use ($organisationId) {
                $query->whereNull('organisation_id')            // rôle système
                    ->orWhere('organisation_id', $organisationId); // rôle de l'orga active
            })
            ->with('permissions') // charge directement les permissions liées
            ->orderBy('name')
            ->get()
            ->map(function ($role) use ($organisationId) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'readonly' => is_null($role->organisation_id), // true si rôle système
                    'permissions' => $role->permissions->map(fn($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                    ]),
                ];
            });

        return Inertia::render('Organisations/Roles/Index', [
            'orgRoles' => $roles,
        ]);
    }


    public function create()
    {


        $orgPermissions = Permission::where('name', 'like', 'ORG_%')->get();

        return Inertia::render('Organisations/Roles/Create', [
            'orgPermissions' => $orgPermissions,
        ]);
    }
    public function show(Role $role)
    {
        if (!str_starts_with($role->name, 'ORG_')) {
            throw ValidationException::withMessages([
                'role' => 'Le rôle spécifié n\'est pas un rôle organisationnel.',
            ]);
        }

        $role->load('permissions');

        return Inertia::render('Organisations/Roles/Show', [
            'role' => $role,
        ]);
    }


    public function edit(Role $role)
    {
        $organisationId = getPermissionsTeamId();

        // Vérification : appartient à l'organisation et n'est pas un rôle système
        if (is_null($role->organisation_id) || $role->organisation_id != $organisationId) {
            abort(403, "Vous n'êtes pas autorisé à modifier ce rôle.");
        }

        // Récupérer les permissions disponibles pour l'orga

        $permissions = Permission::where('name', 'like', 'ORG_%')->get();

        return Inertia::render('Organisations/Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('id')->toArray(),
            ],
            'permissions' => $permissions,
        ]);
    }



    public function update(Request $request, Role $role)
    {
        $organisationId = getPermissionsTeamId();

        // Vérifier que le rôle appartient à l'organisation active
        if (is_null($role->organisation_id) || $role->organisation_id != $organisationId) {
            abort(403, "Vous n'êtes pas autorisé à modifier ce rôle.");
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        try {
            \DB::transaction(function () use ($role, $data) {
                $role->update(['name' => $data['name'], 'organisation_id' => getPermissionsTeamId()]);
                $role->permissions()->sync($data['permissions'] ?? []);
            });

            return redirect()->route('organisations.roles.index')
                ->with('success', 'Rôle mis à jour avec succès.');
        } catch (QueryException $e) {
            // Gestion d'erreur si doublon
            if ($e->getCode() === '23000') {
                return back()
                    ->withErrors(['name' => 'Ce nom de rôle existe déjà pour votre organisation.'])
                    ->withInput();
            }

            // Autres erreurs
            throw $e;
        }
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


        if (str_starts_with($roleName, 'ORG_')) {
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
                'organisation_id' => getPermissionsTeamId(),
            ]);

            $role->permissions()->sync($permissions->pluck('id')->toArray());
        });

        return redirect()
            ->route('organisations.roles.index')
            ->with('success', 'Rôle créé avec succès.');
    }


    public function destroy(Role $role)
    {
        $organisationId = getPermissionsTeamId();

        // Vérifier que le rôle appartient à l'organisation active

        if (is_null($role->organisation_id) || $role->organisation_id != $organisationId) {
            abort(403, "Vous n'êtes pas autorisé à supprimer ce rôle.");
        }

        if ($role->users()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'Impossible de supprimer le rôle car des utilisateurs y sont assignés.');
        }

        $role->delete();

        return redirect()
            ->route('organisations.roles.index')
            ->with('success', 'Rôle supprimé avec succès.');
    }
}
