<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    protected $casts = [
        'created_at' => 'datetime:d, M,Y h:i A',
        'updated_at' => 'datetime:d, M,Y h:i A',
    ];
}
