<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    //Feio Relacionamento
    protected $table = 'campus';

    protected $fillable = [
        'description'
    ];
    protected $guarded = [
        'id'
        //'created_at', 'update_at'
    ];
    public $timestamps = false;

    //relationships
    //Relacionamentos
    public function meals(){
        return $this->hasMany('App\Meal', 'campus_id', 'id');
    }

    public function shifts(){
        return $this->hasMany('App\Shift', 'campus_id', 'id');
    }

    public function students(){
        return $this->hasMany('App\Student', 'campus_id', 'id');
    }

    public function republics(){
        return $this->hasMany('App\Republic', 'campus_id', 'id');
    }

    public function courses(){
        return $this->hasMany('App\Course', 'campus_id', 'id');
    }

    public function users(){
        return $this->hasMany('App\User', 'campus_id', 'id');
    }

    public function schedulings(){
        return $this->hasMany('App\Scheduling', 'campus_id', 'id');
    }

    public function menus(){
        return $this->hasMany('App\Scheduling', 'campus_id', 'id');
    }
}
