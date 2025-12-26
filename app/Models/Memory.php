<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Memory extends Model
{
    protected $fillable = [
        'image_path',
        'description',
        'is_active',
        'photo_date',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'photo_date' => 'date',
        ];
    }
}
