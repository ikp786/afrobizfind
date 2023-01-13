<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $appends = ['imagepath'];
 	public function getImagepathAttribute($value) {
        if($this->image) {
            return  url("storage".$this->image);
        }
    }
}
