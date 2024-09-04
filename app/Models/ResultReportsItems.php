<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultReportsItems extends Model
{
    protected $fillable = [
        'created_by',
        'result_reports_id',
        'component_id'
    ];
    protected $table = 'result_reports_items';
}