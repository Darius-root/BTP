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
        Schema::create('devis_lots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('corps_etat_id')->constrained('corps_etat')->cascadeOnDelete();
            $table->foreignId('devis_id')->constrained('devis_estimatif_quantitatif')->cascadeOnDelete();

            $table->string('code');      // 01.1
            $table->string('intitule');
            $table->integer('ordre')->default(0);

            $table->decimal('sous_total', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devis_lots');
    }
};
