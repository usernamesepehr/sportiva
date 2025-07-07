<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\category_product;
use Illuminate\Database\Eloquent\Relations\HasMany;

class category extends Model
{
    protected $guarded = [];
    protected $table = 'categories';

    public function category_products(): HasMany
    {
        return $this->hasMany(category_product::class);
    }
}
