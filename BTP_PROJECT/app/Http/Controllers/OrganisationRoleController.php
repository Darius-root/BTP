<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class OrganisationRoleController extends Controller
{
    private function ensureOrganisationContext()
    {
        $organisationId = getPermissionsTeamId();

        if ($organisationId === null) {
            abort(403, 'Aucune organisation active. Veuillez sélectionner une organisation.');
        }

        setPermissionsTeamId($organisationId);

        $user = Auth::user();
        $user->unsetRelation('roles')->unsetRelation('permissions');

        return [$user, $organisationId];
    }

    public function index()
    {
        [$user, $organisationId] = $this->ensureOrganisationContext();

        if (!$user->can('ORG_ORGANISATION_ROLE_VIEW')) {
            abort(403, 'Vous ne disposez pas des autorisations nécessaires pour consulter les rôles.');
        }

        $roles = Role::query()
            ->where('name', 'like', 'ORG_%')
            ->where(function ($query) use ($organisationId) {
                $query->whereNull('organisation_id')
                    ->orWhere('organisation_id', $organisationId);
            })
            ->with('permissions')
            ->orderBy('name')
            ->get()
            ->map(fn($role) => [
                'id' => $role->id,
                'name' => $role->name,
                'readonly' => is_null($role->organisation_id),
                'permissions' => $role->permissions->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                ]),
            ]);

        return Inertia::render('Organisations/Roles/Index', [
            'orgRoles' => $roles,
        ]);
    }

    public function create()
    {
        [$user] = $this->ensureOrganisationContext();

        if (!$user->can('ORG_ORGANISATION_ROLE_CREATE')) {
            abort(403, 'Vous n’êtes pas autorisé à créer un rôle.');
        }

        $orgPermissions = Permission::where('name', 'like', 'ORG_%')->orderBy('name')->get();

        return Inertia::render('Organisations/Roles/Create', [
            'orgPermissions' => $orgPermissions,
        ]);
    }

    public function show(Role $role)
    {
        [$user] = $this->ensureOrganisationContext();

        if (!$user->can('ORG_ORGANISATION_ROLE_VIEW')) {
            abort(403, 'Vous n’êtes pas autorisé à consulter ce rôle.');
        }

        if (!str_starts_with($role->name, 'ORG_')) {
            throw ValidationException::withMessages([
                'role' => 'Le rôle sélectionné n’est pas un rôle organisationnel valide.',
            ]);
        }

        $role->load('permissions');

        return Inertia::render('Organisations/Roles/Show', [
            'role' => $role,
        ]);
    }

    public function edit(Role $role)
    {
        [$user, $organisationId] = $this->ensureOrganisationContext();

        if (!$user->can('ORG_ORGANISATION_ROLE_EDIT')) {
            abort(403, 'Vous n’êtes pas autorisé à modifier un rôle.');
        }

        if (is_null($role->organisation_id) || $role->organisation_id !== $organisationId) {
            abort(403, 'Vous n’êtes pas autorisé à modifier ce rôle.');
        }

        $permissions = Permission::where('name', 'like', 'ORG_%')->orderBy('name')->get();

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
        [$user, $organisationId] = $this->ensureOrganisationContext();

        if (!$user->can('ORG_ORGANISATION_ROLE_EDIT')) {
            abort(403, 'Vous n’êtes pas autorisé à modifier un rôle.');
        }

        if (is_null($role->organisation_id) || $role->organisation_id !== $organisationId) {
            abort(403, 'Vous n’êtes pas autorisé à modifier ce rôle.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        try {
            DB::transaction(function () use ($role, $data, $organisationId) {
                $role->update([
                    'name' => strtoupper($data['name']),
                    'organisation_id' => $organisationId,
                ]);

                $role->permissions()->sync($data['permissions'] ?? []);
            });

            return redirect()
                ->route('organisations.roles.index')
                ->with('success', 'Le rôle a été mis à jour avec succès.');
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return back()
                    ->withErrors([
                        'name' => 'Un rôle portant ce nom existe déjà dans votre organisation.',
                    ])
                    ->withInput();
            }

            throw $e;
        }
    }

    public function store(Request $request)
    {
        [$user, $organisationId] = $this->ensureOrganisationContext();

        if (!$user->can('ORG_ORGANISATION_ROLE_CREATE')) {
            abort(403, 'Vous n’êtes pas autorisé à créer un rôle.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $roleName = strtoupper($validated['name']);

        if (!str_starts_with($roleName, 'ORG_')) {
            throw ValidationException::withMessages([
                'name' => 'Le nom du rôle doit commencer par ORG_.',
            ]);
        }

        $permissions = Permission::whereIn('id', $validated['permissions'])->get();

        $invalidPermissions = $permissions->filter(
            fn($permission) => !str_starts_with($permission->name, 'ORG_')
        );

        if ($invalidPermissions->isNotEmpty()) {
            throw ValidationException::withMessages([
                'permissions' => 'Certaines permissions ne correspondent pas au périmètre organisationnel.',
            ]);
        }

        DB::transaction(function () use ($roleName, $permissions, $organisationId) {
            $role = Role::create([
                'name' => $roleName,
                'organisation_id' => $organisationId,
            ]);

            $role->permissions()->sync($permissions->pluck('id')->toArray());
        });

        return redirect()
            ->route('organisations.roles.index')
            ->with('success', 'Le rôle a été créé avec succès.');
    }

    public function destroy(Role $role)
    {
        [$user, $organisationId] = $this->ensureOrganisationContext();

        if (!$user->can('ORG_ORGANISATION_ROLE_DELETE')) {
            abort(403, 'Vous n’êtes pas autorisé à supprimer un rôle.');
        }

        if (is_null($role->organisation_id) || $role->organisation_id !== $organisationId) {
            abort(403, 'Vous n’êtes pas autorisé à supprimer ce rôle.');
        }

        if ($role->users()->exists()) {
            return back()->with(
                'error',
                'Impossible de supprimer ce rôle car des utilisateurs y sont actuellement assignés.'
            );
        }

        $role->delete();

        return redirect()
            ->route('organisations.roles.index')
            ->with('success', 'Le rôle a été supprimé avec succès.');
    }
}
