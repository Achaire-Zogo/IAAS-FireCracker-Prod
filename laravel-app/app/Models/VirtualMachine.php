<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\SshKey;

class VirtualMachine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'vm_id',
        'os_type',
        'vcpu_count',
        'mem_size_mib',
        'status',
        'ip_address',
        'ssh_port',
        'ssh_key_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sshKey()
    {
        return $this->belongsTo(SshKey::class);
    }
}
