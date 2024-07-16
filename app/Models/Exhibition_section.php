<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exhibition_section extends Model
{
    use HasFactory;

    protected $guarded=[];

    public function exhibition(): BelongsTo
    {
        return $this->belongsTo(Exhibition::class);
    }
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
