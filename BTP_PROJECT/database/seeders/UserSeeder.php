<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Organisation;
use App\Models\OrganisationUser;
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
                'password' => Hash::make('password'), 
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
        
        OrganisationUser::create([
            'user_id'         => $user->id,
            'organisation_id' =>  $org->id,
        ]);




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

        OrganisationUser::create([
            'user_id'         => $user->id,
            'organisation_id' =>  $org->id,
        ]);




        app(PermissionRegistrar::class)->setPermissionsTeamId($org->id);
        // Attribution du rôle admin système   
        $user->assignRole('SYSTEM_ADMIN_PLATEFORME');
        app(PermissionRegistrar::class)->setPermissionsTeamId($orgtest->id);
        // Attribution du admin org
        $user->assignRole('ORG_ADMIN');
    }
}
