<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Itensrepublic extends Model
{
    protected $table = 'itens_republic';

    protected $fillable = [
        'responsability','republic_id','student_id'
    ];
    protected $guarded = [
        'id'
    ];
    public $timestamps = false;

    public function student(){
        return $this->belongsTo('App\Student');
    }

    public function republic(){
        return $this->belongsTo('App\Republic');
    }
}
