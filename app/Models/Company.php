<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $with = ['images', 'currency'];

    protected $appends = ['imagepath'];
    public function getImagepathAttribute($value)
    {
        if ($this->image) {
            return  url("storage/" . $this->image);
        }
    }

    public function activeoffers()
    {
        $today = Carbon::today();
        $originalprice = $this->hasMany(Product::class);
        $activeoffers = $this->hasMany(Offer::class)
            ->whereDate('start_date', '<=', $today->format('Y-m-d'))
            ->whereDate('end_date', '>=', $today->format('Y-m-d'))->where('active', 1);

        return $activeoffers;
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function images()
    {
        return $this->hasMany(CompanyImage::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currency()
    {
        return $this->hasOne(Currency::class, "id", "currency_id");
    }

    public function customers()
    {
        return $this->belongsToMany(User::class, 'customers')->select("*", "customers.mobileallowed");
    }
}
