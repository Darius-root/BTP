<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Organisation;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{

    public function run(): void
    {

        User::factory()->count(10)->create();


        // Créons un utilisateur de test
        $user = User::firstOrCreate(
            [
                'email' => 'admin@admin.com',
                'name' => 'SuperAdmin',
                'password' => Hash::make('password'), // mot de passe hashé
            ]
        );

        // Créons une organisation si elle n'existe pas
        $org = Organisation::firstOrCreate(
            [
                'is_system' => true,
                'nom' => 'Système',
                'raison_sociale' => 'Organisation Système',
                'pays' => 'Bénin',
                'devise' => 'XOF',
                'user_id' => $user->id,
            ]
        );

        $orgtest = Organisation::firstOrCreate(
            [
                'is_system' => false,
                'nom' => 'TEST Organisation',
                'raison_sociale' => 'Test  Organisation',
                'pays' => 'Bénin',
                'devise' => 'XOF',
                'user_id' => $user->id,
            ]
        );

        // Récupérons tous les rôles
        $roles = Role::all();

        foreach ($roles as $role) {
            if (str_starts_with($role->name, 'SYSTEM_')) {
                app(PermissionRegistrar::class)->setPermissionsTeamId($org->id);

                // Attribution des rôles système avec organisation   systeme
                $user->assignRole($role->name);
            }

            if (str_starts_with($role->name, 'ORG_')) {
                app(PermissionRegistrar::class)->setPermissionsTeamId($orgtest->id);

                // Attribution des rôles organisationnels avec test organisation
                //pour les role role organisationnel general
                $user->assignRole($role->name);
            }
        }
    }
}
