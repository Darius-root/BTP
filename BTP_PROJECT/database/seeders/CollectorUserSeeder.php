<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Organisation;
use App\Models\OrganisationUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class CollectorUserSeeder extends Seeder
{
    public function run(): void
    {
        // =========================
        // UTILISATEUR COLLECTEUR
        // =========================
        $user = User::firstOrCreate(
            ['email' => 'collecteur@system.local'],
            [
                'name'     => 'Collecteur Système',
                'password' => Hash::make('password'),
            ]
        );

        // =========================
        // ORGANISATION
        // =========================
        $org = Organisation::firstOrCreate(
            [
                'is_system'       => true,
                'nom'             => 'Organisation Système',
                'raison_sociale'  => 'Organisation Collecte',
                'pays'            => 'Bénin',
                'devise'          => 'XOF',
                'user_id'         => $user->id,
            ]
        );

        // =========================
        // LIAISON USER ↔ ORGANISATION
        // =========================
        OrganisationUser::firstOrCreate([
            'user_id'         => $user->id,
            'organisation_id' => $org->id,
        ]);

        // =========================
        // INITIALISATION TEAM (OBLIGATOIRE)
        // =========================
        app(PermissionRegistrar::class)->setPermissionsTeamId($org->id);

        // =========================
        // ASSIGNATION ROLE COLLECTEUR
        // =========================
        $user->assignRole('SYSTEM_COLLECTEUR');
    }
}
