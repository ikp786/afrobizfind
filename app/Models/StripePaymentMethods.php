<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StripePaymentMethods extends Model
{
    use HasFactory;

    public function getPaymentMethodResponceAttribute($val){
        return (json_decode($val));
    }
}
