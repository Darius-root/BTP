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
        Schema::create('collections_prix', function (Blueprint $table) {
            $table->id();

            $table->foreignId('commune_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('arrondissement_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('quartier_id')->nullable();

            $table->foreignId('materiau_id')
                ->constrained('materiaux')
                ->cascadeOnDelete();

            $table->foreignId('devise_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('categorie_id')
                ->constrained('corps_etat')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->text('description_materiaux');
            $table->text('detail')->nullable();

            $table->decimal('price', 15, 2);

          
            $table->boolean('is_validated')->default(false);

            /**
             * Métadonnées de validation (optionnelles mais très utiles)
             */
            $table->foreignId('validated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('validated_at')->nullable();

            $table->string('point_vente')->nullable();

            $table->timestamps();

            /**
             * Index utiles
             */
            $table->index(['is_validated', 'created_at']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections_prix');
    }
};
