<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    public function scopeGetCount($query,$search ='') {
        if($search!=''){
            return $query->where('version','LIKE',"%{$search}%")->count();
        }
        return $query->count();
    }

    public function scopeGetData($query, $filters = array()) {
        $orderby           = (@$filters['orderby'])?$filters['orderby']:'created_at';
        $dir               = (@$filters['dir'])?$filters['dir']:'desc';
        $search            =  @$filters['search'];
        $start             =  @$filters['start'];
        $limit             =  @$filters['limit'];
        $status            =  @$filters['status']; 
        $select            =  @$filters['select'];
         
        $data =  $query->orderBy($orderby, $dir );
        if($status != ''){ $data = $data->where('status',$status); }
        if($search   != ''){ $data = $data->where('version','LIKE',"%{$search}%"); }     
        if($select   != ''){ $data = $data->select($select); }
        if($start    != ''){ $data = $data->offset($start)->limit($limit); }
        return $data->get();
    }
}