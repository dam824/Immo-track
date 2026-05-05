<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['achat', 'location'])->default('achat');
            $table->string('titre')->nullable();
            $table->text('url')->nullable();
            $table->text('thumbnail_url')->nullable();
            $table->string('source', 100)->nullable();
            $table->string('departement', 3)->nullable();
            $table->string('code_postal', 10)->nullable();
            $table->string('ville', 100)->nullable();
            $table->string('quartier', 100)->nullable();
            $table->decimal('superficie', 6, 2)->nullable();
            $table->tinyInteger('nb_pieces')->nullable();
            $table->tinyInteger('nb_chambres')->nullable();
            $table->char('dpe', 1)->nullable();
            $table->boolean('meuble')->default(false);
            $table->decimal('prix_achat', 10, 2)->nullable();
            $table->decimal('prix_m2', 8, 2)->nullable();
            $table->decimal('loyer_mensuel', 8, 2)->nullable();
            $table->decimal('loyer_m2', 6, 2)->nullable();
            $table->boolean('charges_incluses')->default(false);
            $table->decimal('charges_copro', 8, 2)->nullable();
            $table->decimal('taxe_fonciere', 8, 2)->nullable();
            $table->decimal('travaux_estimes', 10, 2)->nullable();
            $table->enum('statut', ['actif','a_visiter','visite','offre_faite','archive','elimine'])->default('actif');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
