<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Organisation;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Communes du Bénin
        $communes = [
            ['code' => 'COT', 'libelle' => 'Cotonou'],
            ['code' => 'PNV', 'libelle' => 'Porto-Novo'],
            ['code' => 'PAR', 'libelle' => 'Parakou'],
            ['code' => 'ABJ', 'libelle' => 'Abomey-Calavi'],
            ['code' => 'DJO', 'libelle' => 'Djougou'],
            ['code' => 'BOH', 'libelle' => 'Bohicon'],
            ['code' => 'NAT', 'libelle' => 'Natitingou'],
            ['code' => 'SAV', 'libelle' => 'Savè'],
            ['code' => 'ABM', 'libelle' => 'Abomey'],
            ['code' => 'OUI', 'libelle' => 'Ouidah'],
            ['code' => 'KAN', 'libelle' => 'Kandi'],
            ['code' => 'LOK', 'libelle' => 'Lokossa'],
            ['code' => 'SEM', 'libelle' => 'Sèmè-Kpodji'],
            ['code' => 'MAL', 'libelle' => 'Malanville'],
            ['code' => 'NKK', 'libelle' => 'Nikki'],
        ];

        foreach ($communes as $commune) {
            DB::table('communes')->insert(array_merge($commune, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 2. Unités de mesure
        $unites = [
            ['code' => 'M2', 'libelle' => 'Mètre carré'],
            ['code' => 'M3', 'libelle' => 'Mètre cube'],
            ['code' => 'ML', 'libelle' => 'Mètre linéaire'],
            ['code' => 'KG', 'libelle' => 'Kilogramme'],
            ['code' => 'T', 'libelle' => 'Tonne'],
            ['code' => 'U', 'libelle' => 'Unité'],
            ['code' => 'L', 'libelle' => 'Litre'],
            ['code' => 'SAC', 'libelle' => 'Sac'],
            ['code' => 'FT', 'libelle' => 'Forfait'],
            ['code' => 'ENS', 'libelle' => 'Ensemble'],
            ['code' => 'M', 'libelle' => 'Mètre'],
            ['code' => 'PA', 'libelle' => 'Paire'],
        ];

        foreach ($unites as $unite) {
            DB::table('unites_mesure')->insert(array_merge($unite, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 3. Devises
        $devises = [
            ['code' => 'XOF', 'libelle' => 'Franc CFA', 'symbole' => 'FCFA'],
            ['code' => 'EUR', 'libelle' => 'Euro', 'symbole' => '€'],
            ['code' => 'USD', 'libelle' => 'Dollar américain', 'symbole' => '$'],
            ['code' => 'GBP', 'libelle' => 'Livre sterling', 'symbole' => '£'],
        ];

        foreach ($devises as $devise) {
            DB::table('devises')->insert(array_merge($devise, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 4. Matériaux (avec relation aux unités)
        $materiaux = [
            ['code' => 'CIM', 'nom' => 'Ciment', 'unite_id' => 8], // SAC
            ['code' => 'SAB', 'nom' => 'Sable', 'unite_id' => 3], // M3
            ['code' => 'GRA', 'nom' => 'Gravier', 'unite_id' => 3], // M3
            ['code' => 'FER', 'nom' => 'Fer à béton', 'unite_id' => 4], // KG
            ['code' => 'BRI', 'nom' => 'Brique', 'unite_id' => 6], // U
            ['code' => 'CAR', 'nom' => 'Carreau', 'unite_id' => 1], // M2
            ['code' => 'PEI', 'nom' => 'Peinture', 'unite_id' => 7], // L
            ['code' => 'BOI', 'nom' => 'Bois de coffrage', 'unite_id' => 3], // M3
            ['code' => 'TUY', 'nom' => 'Tuyau PVC', 'unite_id' => 11], // M
            ['code' => 'TOL', 'nom' => 'Tôle', 'unite_id' => 6], // U
            ['code' => 'VIT', 'nom' => 'Vitre', 'unite_id' => 1], // M2
            ['code' => 'CHA', 'nom' => 'Chaux', 'unite_id' => 8], // SAC
        ];

        foreach ($materiaux as $materiau) {
            DB::table('materiaux')->insert(array_merge($materiau, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 5. Corps d'état (nécessite un user)
        $user = User::first();
        if ($user) {
            $corpsEtat = [
                ['code' => 'GOS', 'intitule' => 'Gros Œuvre', 'ordre' => 1],
                ['code' => 'MAC', 'intitule' => 'Maçonnerie', 'ordre' => 2],
                ['code' => 'CHA', 'intitule' => 'Charpente', 'ordre' => 3],
                ['code' => 'COU', 'intitule' => 'Couverture', 'ordre' => 4],
                ['code' => 'PLO', 'intitule' => 'Plomberie', 'ordre' => 5],
                ['code' => 'ELE', 'intitule' => 'Électricité', 'ordre' => 6],
                ['code' => 'MEN', 'intitule' => 'Menuiserie', 'ordre' => 7],
                ['code' => 'PEI', 'intitule' => 'Peinture', 'ordre' => 8],
                ['code' => 'REV', 'intitule' => 'Revêtement', 'ordre' => 9],
                ['code' => 'ETN', 'intitule' => 'Étanchéité', 'ordre' => 10],
            ];

            foreach ($corpsEtat as $corps) {
                DB::table('corps_etat')->insert(array_merge($corps, [
                    'user_id' => $user->id,
                    'sous_total' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // 6. Clients (nécessite une organisation)
        $organisation = Organisation::first();
        if ($organisation) {
            $clients = [
                [
                    'nom' => 'Jean Doe',
                    'societe' => 'Construction SARL',
                    'email' => 'jean.doe@construction.bj',
                    'telephone' => '+229 97 00 00 01',
                    'adresse' => 'Quartier Cadjèhoun, Cotonou',
                ],
                [
                    'nom' => 'Marie Dupont',
                    'societe' => 'Immobilier Plus',
                    'email' => 'marie.dupont@immobilier.bj',
                    'telephone' => '+229 97 00 00 02',
                    'adresse' => 'Route de l\'Aéroport, Porto-Novo',
                ],
                [
                    'nom' => 'Paul Martin',
                    'societe' => null,
                    'email' => 'paul.martin@email.bj',
                    'telephone' => '+229 97 00 00 03',
                    'adresse' => 'Akpakpa, Cotonou',
                ],
                [
                    'nom' => 'Sophie Bernard',
                    'societe' => 'BTP Services',
                    'email' => 'sophie.bernard@btp.bj',
                    'telephone' => '+229 97 00 00 04',
                    'adresse' => 'Centre-ville, Parakou',
                ],
                [
                    'nom' => 'Thomas Robert',
                    'societe' => 'Promotion Habitat',
                    'email' => 'thomas.robert@habitat.bj',
                    'telephone' => '+229 97 00 00 05',
                    'adresse' => 'Godomey, Abomey-Calavi',
                ],
            ];

            foreach ($clients as $client) {
                DB::table('clients')->insert(array_merge($client, [
                    'organisation_id' => $organisation->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        $this->command->info('✅ Données de référence créées avec succès !');
    }
}
