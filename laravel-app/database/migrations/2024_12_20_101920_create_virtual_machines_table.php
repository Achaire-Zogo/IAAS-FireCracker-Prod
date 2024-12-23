<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('virtual_machines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ssh_key_id')->nullable()->constrained()->onDelete('set null');
            
            // Informations de base
            $table->string('name');
            
            // Caractéristiques physiques
            $table->integer('vcpu_count')->nullable();
            $table->integer('memory_size_mib')->nullable();
            $table->integer('disk_size_gb')->nullable();
            $table->string('kernel_image_path')->nullable();
            $table->string('rootfs_path')->nullable();
            
            // Configuration réseau
            $table->string('mac_address')->unique()->nullable();
            $table->string('ip_address')->unique()->nullable();
            $table->string('tap_device_name')->nullable();
            $table->string('tap_ip')->nullable();
            $table->string('network_namespace')->nullable();
            $table->boolean('allow_mmds_requests')->default(false);
            
            // Configuration de démarrage
            $table->text('boot_args')->nullable();
            $table->boolean('track_dirty_pages')->default(true);
            
            // Métriques et limites
            $table->integer('rx_rate_limiter_bandwidth')->nullable();
            $table->integer('tx_rate_limiter_bandwidth')->nullable();
            $table->integer('balloon_size_mib')->nullable();
            $table->boolean('balloon_deflate_on_oom')->default(true);
            
            // État et surveillance
            $table->enum('status', [
                'creating',
                'created',
                'starting',
                'running',
                'stopping',
                'stopped',
                'error',
                'deleted'
            ])->default('creating');
            
            // Chemins des fichiers
            $table->string('socket_path')->nullable();
            $table->string('log_path')->nullable();
            $table->string('pid_file_path')->nullable();
            
            // Informations de processus
            $table->integer('pid')->nullable();
            $table->timestamp('last_start_time')->nullable();
            $table->timestamp('last_stop_time')->nullable();
            $table->timestamp('last_error_time')->nullable();
            $table->text('last_error_message')->nullable();
            
            // Métriques d'utilisation
            $table->float('cpu_usage_percent')->nullable();
            $table->integer('memory_usage_mib')->nullable();
            $table->integer('disk_usage_bytes')->nullable();
            $table->bigInteger('network_rx_bytes')->nullable();
            $table->bigInteger('network_tx_bytes')->nullable();
            
            // Sécurité et accès
            $table->integer('ssh_port')->nullable();
            $table->string('root_password_hash')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->string('locked_by')->nullable();
            
            // Facturation et utilisation
            $table->timestamp('billing_start_time')->nullable();
            $table->decimal('total_running_hours', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('virtual_machines');
    }
};
