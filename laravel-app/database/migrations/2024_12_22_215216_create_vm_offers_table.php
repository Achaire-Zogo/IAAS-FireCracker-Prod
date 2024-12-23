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
        Schema::create('vm_offers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->integer('cpu_count');
            $table->integer('memory_size_mib');
            $table->integer('disk_size_gb');
            $table->decimal('price_per_hour', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Ajout de la colonne vm_offer_id à la table virtual_machines
        Schema::table('virtual_machines', function (Blueprint $table) {
            $table->foreignId('vm_offer_id')
                  ->nullable()
                  ->constrained()
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('virtual_machines', function (Blueprint $table) {
            $table->dropForeign(['vm_offer_id']);
            $table->dropColumn('vm_offer_id');
        });

        Schema::dropIfExists('vm_offers');
    }
};
