<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Itensrepublic extends Model
{
    protected $table = 'Itensrepublic';

    protected $fillable = [
        'responsability','republic_id','student_id'
    ];
    protected $guarded = [
        'id', 'created_at', 'update_at'
    ];

    public function student(){
        return $this->belongsTo('App\Student');
    }

    public function republic(){
        return $this->belongsTo('App\Republic');
    }
}
