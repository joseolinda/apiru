<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    //Feito relacionamento
    protected $table = 'shift';

    protected $fillable = [
        'description','campus_id'
    ];
    protected $guarded = [
        'id'
        //'created_at', 'update_at'
    ];
    public $timestamps = false;

    //relationships
    //defining relationship between tables Meal and Shift
    //Definindo relação entre as tabela Meal e Shift - Relação M p/ N
    public function meals(){
        return $this->belongsToMany('App\Meal', 'shift_meal', 'idShift', 'idMeal');
    }

    //Relação de Shift com Student
    public function students(){
        return $this->hasMany('App\Student', 'shift_id', 'id');
    }

    //Relação shift com Campus
    public function campus(){
        return $this->belongsTo('App\Campus');
    }

}
