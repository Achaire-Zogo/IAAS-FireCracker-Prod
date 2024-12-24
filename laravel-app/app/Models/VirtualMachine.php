<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\SshKey;
use App\Models\SystemImage;
use App\Models\VmOffer;

class VirtualMachine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'ssh_key_id',
        'system_image_id',
        'vm_offer_id',
        'vcpu_count',
        'memory_size_mib',
        'disk_size_gb',
        'kernel_image_path',
        'rootfs_path',
        'mac_address',
        'ip_address',
        'tap_device_name',
        'tap_ip',
        'network_namespace',
        'allow_mmds_requests',
        'boot_args',
        'track_dirty_pages',
        'rx_rate_limiter_bandwidth',
        'tx_rate_limiter_bandwidth',
        'balloon_size_mib',
        'balloon_deflate_on_oom',
        'status',
        'socket_path',
        'log_path',
        'pid_file_path',
        'pid',
        'last_start_time',
        'last_stop_time',
        'last_error_time',
        'last_error_message',
        'cpu_usage_percent',
        'memory_usage_mib',
        'disk_usage_bytes',
        'network_rx_bytes',
        'network_tx_bytes',
        'ssh_port',
        'root_password_hash',
        'is_locked',
        'locked_at',
        'locked_by',
        'billing_start_time',
        'total_running_hours',
        'total_cost'
    ];

    protected $casts = [
        'allow_mmds_requests' => 'boolean',
        'track_dirty_pages' => 'boolean',
        'balloon_deflate_on_oom' => 'boolean',
        'is_locked' => 'boolean',
        'last_start_time' => 'datetime',
        'last_stop_time' => 'datetime',
        'last_error_time' => 'datetime',
        'locked_at' => 'datetime',
        'billing_start_time' => 'datetime',
        'cpu_usage_percent' => 'float',
        'total_running_hours' => 'decimal:2',
        'total_cost' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sshKey()
    {
        return $this->belongsTo(SshKey::class,'ssh_key_id');
    }

    public function systemImage()
    {
        return $this->belongsTo(SystemImage::class);
    }

    public function vmOffer()
    {
        return $this->belongsTo(VmOffer::class);
    }

    public function generateMacAddress()
    {
        // Générer une adresse MAC unique basée sur l'ID de la VM
        $mac = sprintf('02:FC:%02X:%02X:%02X:%02X',
            ($this->id >> 24) & 0xFF,
            ($this->id >> 16) & 0xFF,
            ($this->id >> 8) & 0xFF,
            $this->id & 0xFF
        );
        $this->mac_address = $mac;
        return $mac;
    }

    public function generateIpAddress()
    {
        // Convertir l'adresse MAC en adresse IP
        // Format: 172.16.x.y où x.y sont dérivés des deux derniers octets de l'adresse MAC
        if (!$this->mac_address) {
            $this->generateMacAddress();
        }

        $mac_parts = explode(':', $this->mac_address);
        $ip = sprintf('172.16.%d.%d',
            hexdec($mac_parts[4]),
            hexdec($mac_parts[5])
        );
        $this->ip_address = $ip;
        return $ip;
    }

    public function isRunning()
    {
        return $this->status === 'running';
    }

    public function isStopped()
    {
        return in_array($this->status, ['stopped', 'created']);
    }

    public function hasError()
    {
        return $this->status === 'error';
    }

    public function updateMetrics(array $metrics)
    {
        $this->update([
            'cpu_usage_percent' => $metrics['cpu_usage'] ?? null,
            'memory_usage_mib' => $metrics['memory_usage'] ?? null,
            'disk_usage_bytes' => $metrics['disk_usage'] ?? null,
            'network_rx_bytes' => $metrics['network_rx'] ?? null,
            'network_tx_bytes' => $metrics['network_tx'] ?? null,
        ]);
    }

    public function updateBilling()
    {
        if ($this->billing_start_time && $this->isRunning()) {
            $hours = now()->diffInHours($this->billing_start_time, true);
            $this->total_running_hours = $hours;
            $this->total_cost = $hours * ($this->vmOffer->price_per_hour ?? 0);
            $this->save();
        }
    }
}
