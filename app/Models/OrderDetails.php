<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetails extends Model
{
    protected $fillable = [
        'position',
    ];
    protected $table = 'order_details';
}