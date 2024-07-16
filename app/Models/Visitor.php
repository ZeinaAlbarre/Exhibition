<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Visitor extends Model
{
    use HasFactory;

    protected $guarded=[];

    public function user(): MorphMany
    {
        return $this->morphMany(User::class,'userable');
    }

}
