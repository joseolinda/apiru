<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PerguntasFormulario extends Model
{
    //

    public function formulario()
    {
        return $this->belongsTo("App\Formulario");
    }

    public function itensPergunta(){
        return $this->hasMany('App\ItemPerguntasFormulario', 'pform_id', 'id');
    }
}
