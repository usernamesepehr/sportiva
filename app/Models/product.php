<?php

namespace App\Models;

use App\Models\User;
use App\Models\order;
use App\Models\like;
use App\Models\cart;
use App\Models\comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class product extends Model
{
    protected $guarded = [];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function carts(): BelongsToMany
    {
        return $this->belongsToMany(cart::class);
    }
    public function order(): BelongsToMany
    {
        return $this->belongsToMany(order::class);
    }
    public function comments(): HasMany
    {
        return $this->hasMany(comment::class);
    }
    public function likes(): HasMany
    {
        return $this->hasMany(like::class);
    }
}
