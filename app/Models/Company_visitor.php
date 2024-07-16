<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company_visitor extends Model
{
    use HasFactory;

    protected $guarded=[];

    public function company(): BelongsToMany
    {
        return $this->belongsToMany(User::class,'company_id','id');
    }

    public function visitor(): BelongsToMany
    {
        return $this->belongsToMany(User::class,'visitor_id','id');
    }
}
