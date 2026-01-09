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
        $systemPermissions = [
            'SYSTEM_USER_CREATE',
            'SYSTEM_USER_DELETE',
            'SYSTEM_ROLE_MANAGE',
        ];

        $systemPermissions = [



            //Utilisateurs
            'SYSTEM_USER_VIEW',
            'SYSTEM_USER_CREATE',
            'SYSTEM_USER_EDIT',
            'SYSTEM_USER_DELETE',
            // Rôles
            'SYSTEM_ROLE_VIEW',
            'SYSTEM_ROLE_CREATE',
            'SYSTEM_ROLE_EDIT',
            'SYSTEM_ROLE_DELETE',

            // Permissions
            'SYSTEM_PERMISSION_VIEW',
            'SYSTEM_PERMISSION_CREATE',
            'SYSTEM_PERMISSION_EDIT',
            'SYSTEM_PERMISSION_DELETE',

            // Communes
            'SYSTEM_COMMUNE_VIEW',
            'SYSTEM_COMMUNE_CREATE',
            'SYSTEM_COMMUNE_EDIT',
            'SYSTEM_COMMUNE_DELETE',

            // Arrondissements
            'SYSTEM_ARRONDISSEMENT_VIEW',
            'SYSTEM_ARRONDISSEMENT_CREATE',
            'SYSTEM_ARRONDISSEMENT_EDIT',
            'SYSTEM_ARRONDISSEMENT_DELETE',

            // Unités de mesure
            'SYSTEM_UNITE_MESURE_VIEW',
            'SYSTEM_UNITE_MESURE_CREATE',
            'SYSTEM_UNITE_MESURE_EDIT',
            'SYSTEM_UNITE_MESURE_DELETE',

            // Matériaux
            'SYSTEM_MATERIAU_VIEW',
            'SYSTEM_MATERIAU_CREATE',
            'SYSTEM_MATERIAU_EDIT',
            'SYSTEM_MATERIAU_DELETE',

            // Devises
            'SYSTEM_DEVISE_VIEW',
            'SYSTEM_DEVISE_CREATE',
            'SYSTEM_DEVISE_EDIT',
            'SYSTEM_DEVISE_DELETE',

            // Corps d’état
            'SYSTEM_CORPS_ETAT_VIEW',
            'SYSTEM_CORPS_ETAT_CREATE',
            'SYSTEM_CORPS_ETAT_EDIT',
            'SYSTEM_CORPS_ETAT_DELETE',
        ];


        foreach ($systemPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // =========================
        // PERMISSIONS ORGANISATION
        // =========================
        $orgPermissions = [
            'ORG_ADD_USER_TO_ORGANISATION',
            'ORG_DEVIS_CREATE',
            'ORG_DEVIS_EDIT',
            'ORG_DEVIS_VALIDATE',
            'ORG_PRIX_CREATE',
            'ORG_PRIX_VALIDATE',

            //organisation
            'ORG_ORGANISATION_VIEW',
            'ORG_ORGANISATION_CREATE',
            'ORG_ORGANISATION_EDIT',
            'ORG_ORGANISATION_DELETE',

            //organisation user
            'ORG_ORGANISATIONUSER_VIEW',
            'ORG_ORGANISATIONUSER_CREATE',
            'ORG_ORGANISATIONUSER_EDIT',
            'ORG_ORGANISATIONUSER_DELETE',

            //organisation role
            'ORG_ORGANISATION_USER_ROLE_ASSIGN',
            'ORG_ORGANISATION_ROLE_VIEW',
            'ORG_ORGANISATION_ROLE_CREATE',
            'ORG_ORGANISATION_ROLE_EDIT',
            'ORG_ORGANISATION_ROLE_DELETE',

            //client
            'ORG_CLIENT_VIEW',
            'ORG_CLIENT_CREATE',
            'ORG_CLIENT_EDIT',
            'ORG_CLIENT_DELETE',

            //projet
            'ORG_PROJET_VIEW',
            'ORG_PROJET_CREATE',
            'ORG_PROJET_EDIT',
            'ORG_PROJET_DELETE',

            //batiment
            'ORG_BATIMENT_VIEW',
            'ORG_BATIMENT_CREATE',
            'ORG_BATIMENT_EDIT',
            'ORG_BATIMENT_DELETE',

        ];

        foreach ($orgPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // =========================
        // ROLES SYSTEME
        // =========================
        $systemRoles = [
            'SYSTEM_ADMIN_PLATEFORME',
            'SYSTEM_COLLECTEUR',
        ];

        foreach ($systemRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // =========================
        // ROLES ORGANISATION
        // =========================
        $orgRoles = [

            'ORG_ADMIN',
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
        $owner = Role::findByName('ORG_ADMIN');
        $owner->givePermissionTo($orgPermissions);



        // ORG_LECTEUR → lecture uniquement
        $lecteur = Role::findByName('ORG_COLLECTEUR');
        $lecteur->givePermissionTo(['ORG_DEVIS_VALIDATE', 'ORG_PRIX_VALIDATE']);
    }
}
