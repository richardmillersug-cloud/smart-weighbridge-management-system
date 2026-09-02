<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CloudSyncLog extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'action',
        'status',
        'attempts',
        'error_message',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
        ];
    }
}
