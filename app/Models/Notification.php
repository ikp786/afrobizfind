<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{

    /*protected $appends = ['data'];
 	public function getDataAttribute($value) {
        if($this->image) {
            return  url("storage/".$this->image);
        }
    }*/

    /*public function company() {
        return $this->belongsTo('App\Company','id','type_id');
    }*/

    protected $appends = ['company_id'];

 	public function getCompanyIdAttribute($value) {
        return $this->type_id;
    }
}
