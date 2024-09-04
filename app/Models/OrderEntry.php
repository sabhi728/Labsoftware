<?php

namespace App\Models;

use App\Http\Controllers\AdminControllers\HomeController;
use Illuminate\Database\Eloquent\Model;

class OrderEntry extends Model
{
    protected $table = 'order_entry';

    public function patients()
    {
        return $this->belongsTo(Patients::class, 'umr_number', 'umr_number');
    }
}
