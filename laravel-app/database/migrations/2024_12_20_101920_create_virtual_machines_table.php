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
        Schema::create('virtual_machines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ssh_key_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('vm_id')->unique();
            $table->string('os_type');
            $table->integer('vcpu_count')->default(1);
            $table->integer('mem_size_mib')->default(1024);
            $table->string('status')->default('stopped');
            $table->string('ip_address')->nullable();
            $table->text('configuration')->nullable();
            $table->integer('ssh_port')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('virtual_machines', function (Blueprint $table) {
            $table->dropForeign(['ssh_key_id']);
            $table->dropColumn(['ssh_key_id', 'ssh_port']);
        });
    }
};
