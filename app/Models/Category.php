<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user(): hasMany
    {
        return $this->hasMany(User::class);
    }

    public function exhibition_category(): HasMany
    {
        return $this->hasMany(Exhibition_category::class);
    }
    public function user_category(): HasMany
    {
        return $this->hasMany(User_category::class);
    }
}
