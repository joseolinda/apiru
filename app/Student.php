<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    //Relacionamento Feito
    protected $table = 'student';

    protected $fillable = [
        'active','dateValid','mat','name','observation','photo','semRegular','campus_id','course_id','shift_id'
    ];
    protected $guarded = [
        'id'
        //,'created_at', 'update_at'
    ];
    public $timestamps = false;


    public function campus(){
        return $this->belongsTo('App\Campus');
    }

    public function shift(){
        return $this->belongsTo('App\Shift');
    }

    public function course(){
        return $this->belongsTo('App\Course');
    }

    public function allowstudenmealdays(){
        return $this->hasMany('App\Allowstudenmealday', 'student_id', 'id');
    }

    public function itensrepublics(){
        return $this->hasMany('App\Itensrepublic', 'student_id', 'id');
    }

    public function schedulings(){
        return $this->hasMany('App\Scheduling', 'student_id', 'id');
    }

    public function user(){
        return $this->hasMany(User::class, 'student_id');
    }
}
