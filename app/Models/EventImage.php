<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventImage extends Model
{

    protected $table = 'event_images';

    protected $appends = ['imagepath'];
    public function getImagepathAttribute($value)
    {
        if ($this->eventimage) {
            return  url($this->eventimage);
        }
    }
}
