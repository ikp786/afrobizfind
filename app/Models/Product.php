<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $with = ['images', 'currency', 'inventory'];

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }
}
