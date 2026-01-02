<?php

namespace Database\Seeders;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
    {

        User::factory()->count(10)->create();


        // Créons un utilisateur de test
        $user = User::firstOrCreate(
            [
                'email' => 'tester@test.com',
                'name' => 'user',
                'password' => Hash::make('password'), // mot de passe hashé
            ]
        );

        // Créons une organisation si elle n'existe pas
        $org = Organisation::firstOrCreate(
            [
                'is_system' => false,
                'nom' => 'Test',
                'raison_sociale' => 'Organisation test',
                'pays' => 'Bénin',
                'devise' => 'XOF',
                'user_id' => $user->id,
            ]
        );

     

        // Récupérons tous les rôles
        $roles = Role::all();

        foreach ($roles as $role) {
         
            if (str_starts_with($role->name, 'ORG_')) {
                app(PermissionRegistrar::class)->setPermissionsTeamId($org->id);

                // Attribution des rôles organisationnels avec test organisation
                //pour les role role organisationnel general
                $user->assignRole($role->name);
            }
        }
    }
}
