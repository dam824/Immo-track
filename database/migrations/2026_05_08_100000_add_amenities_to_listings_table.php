<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->decimal('charges_annuelles_energie', 10, 2)->nullable()->after('travaux_estimes');
            $table->boolean('cave')->default(false)->after('charges_annuelles_energie');
            $table->boolean('parking')->default(false)->after('cave');
            $table->boolean('balcon')->default(false)->after('parking');
            $table->boolean('ascenseur')->default(false)->after('balcon');
            $table->boolean('gardien')->default(false)->after('ascenseur');
            $table->boolean('digicode')->default(false)->after('gardien');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['charges_annuelles_energie', 'cave', 'parking', 'balcon', 'ascenseur', 'gardien', 'digicode']);
        });
    }
};
