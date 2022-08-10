<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemPerguntasFormulario extends Model
{
    //

    public function perguntas()
    {
        return $this->belongsTo("App\PerguntasFormulario");
    }
}
