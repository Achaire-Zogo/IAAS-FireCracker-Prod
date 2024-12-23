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
        Schema::create('system_images', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('os_type');
            $table->string('version');
            $table->text('description');
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        // Ajout de la colonne system_image_id à la table virtual_machines
        Schema::table('virtual_machines', function (Blueprint $table) {
            $table->foreignId('system_image_id')
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
            $table->dropForeign(['system_image_id']);
            $table->dropColumn('system_image_id');
        });
        
        Schema::dropIfExists('system_images');
    }
};
