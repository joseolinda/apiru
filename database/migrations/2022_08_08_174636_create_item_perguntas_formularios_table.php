<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemPerguntasFormulariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_perguntas_formularios', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('pform_id')->unsigned();
            $table->text('texto_item');

            $table->foreign('pform_id')->references('id')->on('perguntas_formularios');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_perguntas_formularios');
    }
}
