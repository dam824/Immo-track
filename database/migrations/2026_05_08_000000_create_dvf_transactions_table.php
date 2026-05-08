<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dvf_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('code_commune', 6);
            $table->string('nom_commune', 100)->nullable();
            $table->date('date_mutation');
            $table->decimal('valeur_fonciere', 12, 2);
            $table->decimal('superficie', 7, 2);
            $table->tinyInteger('nb_pieces')->nullable();
            $table->decimal('prix_m2', 8, 2);
            $table->timestamp('created_at')->useCurrent();
            $table->index(['code_commune', 'date_mutation']);
            $table->index('prix_m2');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dvf_transactions');
    }
};
