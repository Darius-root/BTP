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
        Schema::create('lots_composants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lot_id')->constrained('devis_lots')->cascadeOnDelete();

            $table->string('code');           // 01.1.1
            $table->string('designation');

            $table->foreignId('unite_id')->constrained('unites_mesure');

            $table->decimal('quantite', 15, 2);
            $table->decimal('prix_unitaire', 15, 2);
            $table->decimal('montant', 15, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lot_composants');
    }
};
