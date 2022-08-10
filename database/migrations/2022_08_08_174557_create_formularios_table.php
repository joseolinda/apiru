<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormulariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('formularios', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titulo')->nullable(false);
            $table->integer('created_by');
            $table->integer('campus_id');
            $table->dateTime('liberar_form');
            $table->dateTime('ocultar_form');
            $table->boolean('obrigatorio')->default(true);
            $table->enum('status_form', ['rascunho', 'publicado', 'finalizado'])->default('rascunho');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('formularios');
    }
}
