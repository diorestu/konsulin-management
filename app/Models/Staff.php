<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    public const TYPES = ['accounting', 'tax', 'legal', 'marketing', 'it'];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'type',
        'position',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class)->withPivot('role')->withTimestamps();
    }
}
