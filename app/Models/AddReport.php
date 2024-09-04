<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddReport extends Model
{
    protected $table = 'add_report';
    protected $primaryKey = 'report_id';

    public function orderType()
    {
        return $this->belongsTo(OrderType::class, 'order_order_type', 'id');
    }
}
