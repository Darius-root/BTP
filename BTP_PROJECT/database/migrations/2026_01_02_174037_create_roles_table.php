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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('libelle', 100);
            $table->enum('scope', ['SYSTEME', 'ORGANISATION'])->default('ORGANISATION');
            $table->timestamps();

            // Index sur le code pour les recherches
            $table->index('code');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }


};
