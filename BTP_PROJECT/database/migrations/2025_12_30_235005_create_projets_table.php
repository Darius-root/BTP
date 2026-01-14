<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devise_id')->constrained('devises')->onDelete('cascade');
            $table->integer('tva')->default(0);
            $table->string('code_projet')->unique();
            $table->string('nom');
            $table->string('localisation')->nullable();
            $table->text('resume')->nullable();
            $table->decimal('budget_previsionnel', 15, 2)->nullable();
            $table->string('type_projet')->nullable()->comment('Résidentiel, Tertiaire, Routier, etc.');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('organisation_id')->constrained('organisations')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projets');
    }
};
