<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradeRespostaFormulariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('grade_resposta_formularios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $form_resp = $table->foreignIdFor(RespostaFormulario::class)->constrained();
            $pergunta = $table->foreignIdFor(PerguntasFormulario::class)->constrained();
            $table->text('resposta_texto')->nullable();
            $table->float('resposta_numero')->nullable();
            $table->unsignedBigInteger('resposta_multipla')->nullable();
            $table->json('resposta_selecao')->nullable();
            $table->timestamps();

            $table->unique($form_resp, $pergunta);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grade_resposta_formularios');
    }
}
