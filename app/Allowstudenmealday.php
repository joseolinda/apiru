<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Allowstudenmealday extends Model
{
    //Feito relacionamento
    protected $table = 'Allowstudenmealday';

    protected $fillable = [
        'friday','monday','saturday','thursday','tuesday','wednesday','student_id','meal_id'
    ];
    protected $guarded = [
        'id'
        //, 'created_at', 'update_at'
    ];
    public $timestamps = false;

    public function meal(){
        return $this->belongsTo('App\Meal');
    }

    public function student(){
        return $this->belongsTo('App\Student');
    }
}
