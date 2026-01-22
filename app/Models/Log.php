<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'model',
        'model_id',
        'action',
        'old_data',
        'new_data',
        'changed_fields',
        'user_id',
        'ip_address',
        'is_rolled_back',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'is_rolled_back' => 'boolean',
    ];
}
