<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class order extends Model
{
    protected $table = 'orders';
    // public $timestamps=false;
    protected $casts = [
        'created_at' => 'datetime:d, M,Y h:i A',
        'updated_at' => 'datetime:d, M,Y h:i A',
    ];

    public function images()
    {
        return $this->hasMany(EventImage::class, "event_id", "event_id");
    }

    public function productimages()
    {
        return $this->hasMany(ProductImage::class, "product_id", "productid");
    }

    public function ticket_type()
    {
        return $this->belongsTo(TicketType::class, 'ticket_type_id', 'id');
    }


}
