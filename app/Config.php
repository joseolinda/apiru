<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $table = 'config';

    protected $fillable = [
        'pathPhotoStudent','pathReport','version'
    ];
    protected $guarded = [
        'id', 'created_at', 'update_at'
    ];
}
