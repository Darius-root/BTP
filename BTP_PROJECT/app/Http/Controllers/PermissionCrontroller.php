<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionCrontroller extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
      
        // Récupérer toutes les permissions
        $permissions = Permission::all();

        // Séparer les permissions par nomenclature
        $systemPermissions = $permissions->filter(function ($perm) {
            return str_starts_with($perm->name, 'SYSTEM_');
        });

        $orgPermissions = $permissions->filter(function ($perm) {
            return str_starts_with($perm->name, 'ORG_');
        });

        return inertia ('Permissions/Index', [
           
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
