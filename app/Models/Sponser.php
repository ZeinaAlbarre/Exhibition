<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sponser extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function exhibition_sponser(): HasMany
    {
        return $this->hasMany(Exhibition_sponser::class);
    }
}
