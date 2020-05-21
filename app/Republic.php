<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Republic extends Model
{
    protected $table = 'republic';

    protected $fillable = [
        'address','city','description','neighborhood','ownerRepublic','valueRepublic','campus_id'
    ];
    protected $guarded = [
        'id'
        //, 'created_at', 'update_at'
    ];
    public $timestamps = false;

    public function campus(){
        return $this->belongsTo('App\Campus');
    }

    public function itensrepublics(){
        return $this->hasMany('App\Itensrepublic', 'republic_id', 'id');
    }


}
