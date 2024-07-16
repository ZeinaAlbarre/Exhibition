<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Company extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user(): MorphMany
    {
        return $this->morphMany(User::class,'userable');
    }
    public function company_stand(): HasMany
    {
        return $this->hasMany(Company_stand::class);
    }



}
