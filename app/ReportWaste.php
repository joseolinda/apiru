<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportWaste extends Model
{
    public $fillable = [
        'startDate', 'endDate', 'content'
    ];
}
