<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bordereaux', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('annee');
            $table->string('version');
            $table->timestamps();
            $table->index('user_id');
            $table->index('annee');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bordereaux');
    }
};
