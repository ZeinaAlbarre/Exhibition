<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company_stand extends Model
{
    use HasFactory;

    protected $guarded=[];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function stand(): BelongsTo
    {
        return $this->belongsTo(Stand::class);
    }
}
