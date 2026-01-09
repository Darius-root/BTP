<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class PermissionCrontroller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        
        // Vérifier que l'utilisateur a au moins une des permissions
        if (!$user->canAny(['SYSTEM_PERMISSION_VIEW', 'ORG_ORGANISATIONUSER_VIEW'])) {
            abort(403, "Vous n'avez pas la permission de consulter les permissions.");
        }

        $permissions = Permission::all();

        $systemPermissions = $permissions->filter(fn($perm) => str_starts_with($perm->name, 'SYSTEM_'));
        $orgPermissions = $permissions->filter(fn($perm) => str_starts_with($perm->name, 'ORG_'));

        return inertia('Permissions/Index', [
            'systemPermissions' => $systemPermissions,
            'orgPermissions' => $orgPermissions,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
