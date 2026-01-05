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
        Schema::create('bordereau_designations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->index();
            $table->string('designation');
            $table->text('caracteristiques');
            $table->string('unite_mesure', 20)->nullable();
            $table->decimal('bi', 15, 2)->nullable();
            $table->decimal('bs', 15, 2)->nullable();
            $table->foreignId('bordereau_id')
                ->constrained('bordereaux')
                ->onDelete('cascade');
            $table->timestamps();

            $table->unique(['bordereau_id', 'code'], 'bordereau_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bordereau_designations');
    }
};
