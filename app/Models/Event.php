<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{

    protected $appends = ['imagepath'];
    public function getImagepathAttribute($value)
    {
        if ($this->flyerimage) {
            return  url("mainflyer/" . $this->flyerimage);
        }
    }

    public function event_currency()
    {
        return $this->belongsTo('App\Models\Currency', 'currency_id', 'id');
    }

    public function ticket_types()
    {
        return $this->hasMany(TicketType::class);
    }
}
