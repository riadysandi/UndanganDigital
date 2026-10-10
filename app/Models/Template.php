<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'thumbnail_path',
        'default_design_json',
        'schema_version',
        'is_premium',
        'is_active',
    ];

    protected $casts = [
        'default_design_json' => 'array',
        'is_premium' => 'boolean',
        'is_active' => 'boolean',
        'schema_version' => 'integer',
    ];

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }
}
