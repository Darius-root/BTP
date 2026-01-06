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
     Schema::create('organisations', function (Blueprint $table) {
    $table->id();
    $table->boolean('is_system')->default(false);
    $table->string('nom');
    $table->string('raison_sociale');
    $table->string('logo')->nullable();
    $table->text('adresse')->nullable();
    $table->string('pays', 100);
    $table->string('devise', 10);
    $table->foreignId('user_id')->constrained('users');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisations');
    }
};
