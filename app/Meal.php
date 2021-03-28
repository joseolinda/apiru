<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    //Relacionamento feito
    protected $table = 'meal';

    protected $fillable = [
        'description','qtdTimeReservationEnd','qtdTimeReservationStart','timeEnd','timeStart','campus_id'
    ];
    protected $guarded = [
        'id'
        //'created_at', 'update_at'
    ];
    public $timestamps = false;

    //relationships
    //defining relationship between tables Meal and Shift
    //Definindo relação entre as tabela Meal e Shift - Relação M p/ N
    public function shifts(){
        return $this->belongsToMany('App\Shift', 'shift_meal', 'idMeal', 'idShift');
    }

    public function campus(){
        return $this->belongsTo('App\Campus');
    }

    //Relação de Meal com Menu
    public function menus(){
        return $this->hasMany('App\Menu', 'meal_id', 'id');
    }
    //Relação de Meal com scheduling
    public function schedulings(){
        return $this->hasMany('App\Scheduling', 'meal_id', 'id');
    }

    //Relação de Meal com allowstudenmealdays
    public function allowstudenmealdays(){
        return $this->hasMany('App\Allowstudenmealday', 'meal_id', 'id');
    }
}
