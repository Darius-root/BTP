<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bordereaux', function (Blueprint $table) {
            $table->id();
            $table->string('nom_bordereau');
            $table->string('annee', 10);
            $table->string('version', 20);
            $table->boolean('actif')->default(false);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Contrainte : un bordereau unique par (nom, année, version)
            $table->unique(['nom_bordereau', 'annee', 'version'], 'bordereau_unique');
            $table->index(['annee', 'version']);
            $table->index('actif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bordereaux');
    }
};
