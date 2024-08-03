<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    public function userable(): MorphTo
    {
        return $this->morphTo();
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class,'mediable');
    }

    public function notification(): MorphMany
    {
        return $this->morphMany(Notification::class,'mediable');
    }
    public function qr(): HasMany
    {
        return $this->hasMany(Qr::class);
    }
    public function favorite(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function payment(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
    public function product(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function rate(): HasMany
    {
        return $this->hasMany(Rate::class);
    }


    public function user_category(): HasMany
    {
        return $this->hasMany(User_category::class);
    }

    public function exhibition_organizer(): HasMany
    {
        return $this->hasMany(Exhibition_organizer::class);
    }
    public function exhibition_company(): HasMany
    {
        return $this->hasMany(Exhibition_company::class);
    }
    public function exhibition_visitor(): HasMany
    {
        return $this->hasMany(Exhibition_visitor::class);
    }

    public function exhibition_employee(): HasMany
    {
        return $this->hasMany(Exhibition_employee::class);
    }

    public function scheduale_visitor(): HasMany
    {
        return $this->hasMany(Scheduale_visitor::class);
    }

    public function company_visitor()
    {
        return $this->hasMany(Company_visitor::class);
    }


    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'password_confirmation',
        'userable_id',
        'userable_type',
        'token',
        'code',
        'expire_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
       // 'email_verified_at' => 'datetime',
        'password'=>'hashed'
    ];
}
