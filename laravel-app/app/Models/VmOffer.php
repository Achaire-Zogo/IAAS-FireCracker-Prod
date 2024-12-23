<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VmOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'cpu_count',
        'memory_size_mib',
        'disk_size_gb',
        'price_per_hour',
        'is_active'
    ];

    protected $casts = [
        'cpu_count' => 'integer',
        'memory_size_mib' => 'integer',
        'disk_size_gb' => 'integer',
        'price_per_hour' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function virtualMachines()
    {
        return $this->hasMany(VirtualMachine::class);
    }
}
