<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    //Relacionamento Feito
    protected $table = 'course';

    protected $fillable = [
        'description','initials','campus_id'
    ];
    protected $guarded = [
        'id'
        //'created_at', 'update_at'
    ];
    public $timestamps = false;
    //relationships
    //Relacionamentos

    public function students(){
        return $this->hasMany('App\Student', 'course_id', 'id');
    }

    public function campus(){
        return $this->belongsTo('App\Campus');
    }
}
