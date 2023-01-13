<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketPurchase extends Model
{
    protected $table = 'ticket_purchase';

    public function images()
    {
        return $this->hasMany(EventImage::class, "event_id", "event_id");
    }

    public function eventname()
    {
        return $this->hasOne(Event::class, "id", "event_id");
    }
}
