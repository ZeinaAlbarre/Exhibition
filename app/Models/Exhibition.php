<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exhibition extends Model
{
    use HasFactory;
    Use SoftDeletes;

    protected $guarded = [];

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class,'mediable');
    }

    public function stand(): HasMany
    {
        return $this->hasMany(Stand::class);
    }

    public function Rate(): HasMany
    {
        return $this->hasMany(Rate::class);
    }

    public function favorite(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function qr(): HasMany
    {
        return $this->hasMany(Qr::class);
    }

    public function scheduale(): HasMany
    {
        return $this->hasMany(Scheduale::class);
    }

    public function exhibition_visitor(): HasMany
    {
        return $this->hasMany(Exhibition_visitor::class);
    }
    public function exhibition_employee(): HasMany
    {
        return $this->hasMany(Exhibition_employee::class);
    }
    public function exhibition_organizer(): HasMany
    {
        return $this->hasMany(Exhibition_organizer::class);
    }

    public function exhibition_category(): HasMany
    {
        return $this->hasMany(Exhibition_category::class);
    }
    public function exhibition_sponser(): HasMany
    {
        return $this->hasMany(Exhibition_sponser::class);
    }
    public function exhibition_company(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Exhibition_section::class);
    }



}
