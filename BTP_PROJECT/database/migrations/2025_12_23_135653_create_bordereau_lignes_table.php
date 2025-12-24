<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bordereau_lignes', function (Blueprint $table) {
            $table->id();
            $table->string('designation');
            $table->text('specification_technique')->nullable();
            $table->string('code');
            $table->decimal('bi', 15, 2)->nullable();
            $table->decimal('bs', 15, 2)->nullable();
            $table->foreignId('bordereau_id')
                ->constrained('bordereaux')
                ->onDelete('cascade');
            $table->timestamps();


            $table->unique(['bordereau_id', 'code', 'designation'], 'bordereau_ligne_unique');

            $table->index(['bordereau_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bordereau_lignes');
    }
};
