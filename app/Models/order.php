<?php

namespace App\Models;


use App\Models\User;
use App\Models\product;
use App\Models\cart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class order extends Model
{
    protected $guarded = [];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function product(): HasOne
    {
        return $this->hasOne(product::class);
    }
}
