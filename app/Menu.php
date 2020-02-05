<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    //Relacionamento Feito
    protected $table = 'Menu';

    protected $fillable = [
        'date','description','campus_id','meal_id'
    ];
    protected $guarded = [
        'id'
        //,'created_at', 'update_at'
    ];
    public $timestamps = false;

    public function campus(){
        return $this->belongsTo('App\Campus');
    }

    public function meal(){
        return $this->belongsTo('App\Meal');
    }

    //Relação de Menu com scheduling
    public function schedulings(){
        return $this->hasMany('App\Scheduling', 'menu_id', 'id');
    }
}
