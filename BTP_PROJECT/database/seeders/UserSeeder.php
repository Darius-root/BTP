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
        // Créons un utilisateur de test
        $user = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'), // mot de passe hashé
            ]
        );

        // Créons une organisation si elle n'existe pas
        $org = Organisation::firstOrCreate(
            ['raison_sociale' => 'Organisation Démo'],
            [
                'pays' => 'Bénin',
                'devise' => 'XOF',
                'user_id' => $user->id,
            ]
        );

        // Récupérons tous les rôles
        $roles = Role::all();

        foreach ($roles as $role) {
            if (str_starts_with($role->name, 'SYSTEM_')) {
                app(PermissionRegistrar::class)->setPermissionsTeamId(0);

                // Attribution des rôles système avec organisation_id = 0
                $user->assignRole($role->name);
            }

            if (str_starts_with($role->name, 'ORG_')) {
                app(PermissionRegistrar::class)->setPermissionsTeamId(1);

                // Attribution des rôles organisationnels avec organisation_id = $org->id
                $user->assignRole($role->name);
            }
        }
    }
}
