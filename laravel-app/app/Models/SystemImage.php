<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image_path',
        'os_type',
        'version'
    ];

    public function virtualMachines()
    {
        return $this->hasMany(VirtualMachine::class);
    }
}
