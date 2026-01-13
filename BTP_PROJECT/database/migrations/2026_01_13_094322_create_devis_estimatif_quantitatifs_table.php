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
        Schema::create('devis_estimatif_quantitatif', function (Blueprint $table) {
            $table->id();

            $table->foreignId('batiment_id')->nullable()->constrained()->nullOnDelete();

            $table->string('intitule');
            $table->string('code')->unique();

            $table->enum('statut', ['brouillon', 'valide'])->default('brouillon');

            $table->boolean('is_template')->default(false);

            $table->foreignId('template_id')
                ->nullable()
                ->constrained('devis_estimatif_quantitatif')
                ->nullOnDelete();

            $table->foreignId('created_by')->constrained('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devis_estimatif_quantitatifs');
    }
};
