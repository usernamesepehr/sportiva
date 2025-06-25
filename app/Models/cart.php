<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\product;
use App\Models\User;
use App\Models\order;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class cart extends Model
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
    public function order():HasOne
    {
        return $this->hasOne(order::class);
    }
}
