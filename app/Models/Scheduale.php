<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scheduale extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function exhibition(): BelongsTo
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function scheduale_visitor(): HasMany
    {
        return $this->hasMany(Scheduale_visitor::class);
    }
}
