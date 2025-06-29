<?php

namespace App\Models;

use App\Models\User;
use App\Models\order;
use App\Models\like;
use App\Models\cart;
use App\Models\comment;
use App\Models\category_product;
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
    public function carts(): HasMany
    {
        return $this->hasMany(cart::class);
    }
    public function order(): HasMany
    {
        return $this->hasMany(order::class);
    }
    public function comments(): HasMany
    {
        return $this->hasMany(comment::class);
    }
    public function likes(): HasMany
    {
        return $this->hasMany(like::class);
    }
    public function category_products(): HasMany
    {
        return $this->hasMany(category_product::class);
    }
}
