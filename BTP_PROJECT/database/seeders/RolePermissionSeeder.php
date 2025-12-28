<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // =========================
        // PERMISSIONS SYSTEME
        // =========================
        $systemPermissions = [
            'SYSTEM_USER_CREATE',
            'SYSTEM_USER_DELETE',
            'SYSTEM_ROLE_MANAGE',
        ];

        foreach ($systemPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // =========================
        // PERMISSIONS ORGANISATION
        // =========================
        $orgPermissions = [
            'ORG_DEVIS_CREATE',
            'ORG_DEVIS_EDIT',
            'ORG_DEVIS_VALIDATE',
            'ORG_PRIX_CREATE',
            'ORG_PRIX_VALIDATE',
        ];

        foreach ($orgPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // =========================
        // ROLES SYSTEME
        // =========================
        $systemRoles = [
            'SYSTEM_ADMIN_PLATEFORME',
            'SYSTEM_SUPERUSER',
        ];

        foreach ($systemRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // =========================
        // ROLES ORGANISATION
        // =========================
        $orgRoles = [
            'ORG_OWNER',
            'ORG_ADMIN',
            'ORG_LECTEUR',
            'ORG_COLLECTEUR',
        ];

        foreach ($orgRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // =========================
        // ATTRIBUTION DES PERMISSIONS AUX ROLES
        // =========================

        // SYSTEM_ADMIN_PLATEFORME → toutes les permissions système
        $adminPlateforme = Role::findByName('SYSTEM_ADMIN_PLATEFORME');
        $adminPlateforme->givePermissionTo($systemPermissions);

        // ORG_OWNER → toutes les permissions organisationnelles
    $owner = Role::findByName('ORG_OWNER');
        $owner->givePermissionTo($orgPermissions);

        // ORG_ADMIN → certaines permissions organisationnelles
        $admin = Role::findByName('ORG_ADMIN');
        $admin->givePermissionTo(['ORG_DEVIS_CREATE', 'ORG_DEVIS_EDIT']);

        // ORG_LECTEUR → lecture uniquement
        $lecteur = Role::findByName('ORG_LECTEUR');
        $lecteur->givePermissionTo(['ORG_DEVIS_VALIDATE', 'ORG_PRIX_VALIDATE']);
    }
}
