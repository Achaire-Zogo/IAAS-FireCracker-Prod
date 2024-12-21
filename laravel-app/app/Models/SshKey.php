<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SshKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'public_key',
        'private_key',
        'name',
    ];

    protected $hidden = [
        'private_key',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function virtualMachines()
    {
        return $this->hasMany(VirtualMachine::class);
    }
}
