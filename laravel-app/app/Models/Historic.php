<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\VirtualMachine;

class Historic extends Model
{
    protected $fillable = [
        'vm_id',
        'status',
        'date'
    ];

    /**
     * Get the virtual machine associated with this historic entry.
     */
    public function virtualMachine(): BelongsTo
    {
        return $this->belongsTo(VirtualMachine::class, 'vm_id');
    }
}
