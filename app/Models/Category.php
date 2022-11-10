<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    protected $appends = ['imagepath'];

    public function getImagepathAttribute($value)
    {
        if ($this->image) {
            return  url($this->image);
            //return  url("storage/".$this->image);
        }
    }


    /**
     * Get the companies for this model.
     */
    public function companies()
    {
        return $this->hasMany('App\Models\Company');
    }
}
