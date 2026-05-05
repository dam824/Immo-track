<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulations', function (Blueprint $table) {
            $table->id();
            $table->string('titre')->nullable();
            $table->foreignId('listing_achat_id')->nullable()->constrained('listings')->nullOnDelete();
            $table->foreignId('listing_location_id')->nullable()->constrained('listings')->nullOnDelete();
            $table->decimal('montant_emprunt', 10, 2)->default(0);
            $table->decimal('taux_interet', 5, 3)->default(0);
            $table->tinyInteger('duree_ans')->default(20);
            $table->decimal('mensualite_credit', 8, 2)->default(0);
            $table->decimal('loyer_retenu', 8, 2)->default(0);
            $table->decimal('charges_copro', 8, 2)->default(0);
            $table->decimal('taxe_fonciere_mois', 8, 2)->default(0);
            $table->decimal('assurance_pno', 8, 2)->default(0);
            $table->decimal('cashflow_mensuel', 8, 2)->default(0);
            $table->decimal('rendement_brut', 5, 2)->default(0);
            $table->decimal('rendement_net', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulations');
    }
};
