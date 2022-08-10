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
            $table->increments('id');
            $form_resp = $table->integer('respform_id')->unsigned();
            $pergunta = $table->integer('pform_id')->unsigned();
            $table->text('resposta_texto')->nullable();
            $table->float('resposta_numero')->nullable();
            $table->integer('resposta_multipla')->unsigned()->nullable();
            $table->json('resposta_selecao')->nullable();
            $table->timestamps();

            $table->foreign('respform_id')->references('id')->on('resposta_formularios');
            $table->foreign('pform_id')->references('id')->on('perguntas_formularios');
            $table->foreign('resposta_multipla')->references('id')->on('item_perguntas_formularios');
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
