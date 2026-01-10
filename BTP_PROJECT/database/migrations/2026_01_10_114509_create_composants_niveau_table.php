<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('composants_niveau', function (Blueprint $table) {
            $table->id();

            $table->foreignId('niveau_id')
                  ->constrained('niveaux_batiment')
                  ->cascadeOnDelete();

            $table->foreignId('devis_estimatif_id')
                  ->constrained('devis_estimatif')
                  ->cascadeOnDelete();

            $table->string('code');      // AS01, AS02...
            $table->string('piece');     // Hall, Bureau, Toilette...

            $table->foreignId('unite_id')
                  ->constrained('unites_mesure')
                  ->restrictOnDelete();

            $table->decimal('qte', 12, 2);
            $table->decimal('prix_unitaire', 15, 2);
            $table->decimal('montant', 18, 2);

            $table->timestamps();

            // Sécurité métier
            $table->unique(
                ['devis_estimatif_id', 'code'],
                'unique_code_par_devis'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('composants_niveau');
    }
};
