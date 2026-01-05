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
            $table->foreignId('commune_id')->constrained()->onDelete('cascade');
            $table->foreignId('arrondissement_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('quartier_id')->nullable();
            $table->foreignId('materiau_id')->constrained('materiaux')->onDelete('cascade'); 
            $table->foreignId('devise_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('categorie_id')->constrained('corps_etat')->onDelete('cascade');
            $table->text('description_materiaux');
            $table->text('detail')->nullable();
            $table->decimal('price', 15, 2);
            $table->boolean('status')->default(true);
            $table->string('point_vente')->nullable();
            $table->timestamps();
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
