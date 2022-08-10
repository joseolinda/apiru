<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Formulario extends Model
{
    //
    public function perguntas(){
        return $this->hasMany('App\PerguntasFormulario', 'form_id', 'id')->with('itensPergunta');
    }
}
