<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Scheduling extends Model
{
    //Relacionamento Feito
    protected $table = 'scheduling';

    protected $fillable = [
        'absenceJustification','canceled_by_student','date','dateInsert','ticketCode','time','wasPresent','campus_id','meal_id','menu_id','student_id','user_id'
    ];
    protected $guarded = [
        'id'
        //, 'created_at', 'update_at'
    ];
    public $timestamps = false;

    //Relações 1 p/ N
    public function campus(){
        return $this->belongsTo('App\Campus');
    }
    public function meal(){
        return $this->belongsTo('App\Meal');
    }
    public function student(){
        return $this->belongsTo('App\Student');
    }
    public function menu(){
        return $this->belongsTo('App\Menu');
    }
    public function user(){
        return $this->belongsTo('App\User');
    }
}
