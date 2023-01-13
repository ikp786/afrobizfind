<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    public $timestamps = false;


    protected $fillable = [
        'name', 'currency_code', 'country_name', 'country_price', 'currency_sign', 'price_in_uk', 'price_per_ticket', 'processing_fee'
    ];

    public function scopeGetCount($query, $search = '')
    {
        if ($search != '') {
            return $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('name', 'LIKE', "%{$search}%")
                ->orWhere('currency_code', 'LIKE', "%{$search}%")
                ->orWhere('currency_sign', 'LIKE', "%{$search}%")
                ->orWhere('country_name', 'LIKE', "%{$search}%")
                ->orWhere('country_price', 'LIKE', "%{$search}%")
                ->orWhere('price_in_uk', 'LIKE', "%{$search}%")
                ->orWhere('price_per_ticket', 'LIKE', "%{$search}%")
                ->orWhere('processing_fee', 'LIKE', "%{$search}%")
                ->orWhere('stripe_support', 'LIKE', "%{$search}%")
                ->count();
        }
        return $query->count();
    }

    public function scopeGetData($query, $filters = array())
    {
        $orderby           = (@$filters['orderby']) ? $filters['orderby'] : 'name';
        $dir               = (@$filters['dir']) ? $filters['dir'] : 'asc';
        $search            =  @$filters['search'];
        $start             =  @$filters['start'];
        $limit             =  @$filters['limit'];
        $status            =  @$filters['status'];
        $select            =  @$filters['select'];

        $data =  $query->orderBy($orderby, $dir);

        if ($search   != '') {
            $data = $data->where('name', 'LIKE', "%{$search}%")
                ->orWhere('name', 'LIKE', "%{$search}%")
                ->orWhere('currency_code', 'LIKE', "%{$search}%")
                ->orWhere('currency_sign', 'LIKE', "%{$search}%")
                ->orWhere('country_name', 'LIKE', "%{$search}%")
                ->orWhere('country_price', 'LIKE', "%{$search}%")
                ->orWhere('price_in_uk', 'LIKE', "%{$search}%")
                ->orWhere('price_per_ticket', 'LIKE', "%{$search}%")
                ->orWhere('processing_fee', 'LIKE', "%{$search}%")
                ->orWhere('stripe_support', 'LIKE', "%{$search}%");
        }
        if ($select   != '') {
            $data = $data->select($select);
        }
        if ($start    != '') {
            $data = $data->offset($start)->limit($limit);
        }
        return $data->get();
    }

    public function getcurrency()
    {
        return $this->hasOne(Currency::class, 'currency_id', 'id');
    }
}
