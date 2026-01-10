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
       Schema::create('devis_estimatif', function (Blueprint $table) {
            $table->id();

            $table->foreignId('batiment_id')
                  ->nullable()
                  ->constrained('batiments')
                  ->nullOnDelete();

            $table->string('intitule');
            $table->string('code')->unique();

            $table->enum('statut', ['brouillon', 'valide'])
                  ->default('brouillon');

            $table->boolean('is_template')->default(false);

            $table->foreignId('template_id')
                  ->nullable()
                  ->constrained('template_devis')
                  ->nullOnDelete();

            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devis_estimatif');
    }
};
