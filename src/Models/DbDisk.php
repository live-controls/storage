<?php

namespace LiveControls\Storage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DbDisk extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'driver',
        'root',
        'throw',
        'key',
        'secret',
        'region',
        'bucket',
        'url',
        'endpoint',
        'use_path_style_endpoint',
        'visibility',
    ];

    protected $casts = [
        'use_path_style_endpoint' => 'boolean',
        'throw' => 'boolean'
    ];
}
