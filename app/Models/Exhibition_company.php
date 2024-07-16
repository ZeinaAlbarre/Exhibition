<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exhibition_company extends Model
{
    use HasFactory;

    protected $guarded=[];

    public function exhibition(): BelongsTo
    {
        return $this->belongsTo(Exhibition::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
