<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyer_references', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('annee')->unsigned()->index();
            $table->string('secteur', 200)->index();          // nom brut du secteur OLL
            $table->string('ville_normalized', 200)->index(); // strtolower + translit ASCII
            $table->string('typo_logt', 10);                  // T1, T2, T3, T4, T4+, T5+
            $table->tinyInteger('nb_pieces')->unsigned()->nullable(); // 1,2,3,4 ou null
            $table->decimal('loyer_m2_median', 6, 2)->nullable();
            $table->decimal('loyer_median', 8, 2)->nullable();
            $table->integer('nb_obs')->unsigned()->nullable();
            $table->timestamps();

            $table->index(['ville_normalized', 'nb_pieces', 'annee']);
            $table->unique(['annee', 'secteur', 'typo_logt'], 'oll_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyer_references');
    }
};
