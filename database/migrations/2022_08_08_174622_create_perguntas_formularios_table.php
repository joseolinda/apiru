<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePerguntasFormulariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('perguntas_formularios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('titulo_pergunta');
            $table->boolean('obrigatorio')->default(true);
            $table->boolean('emabaralhar_itens')->default(false);
            $table->enum('tipo-pergunta', ['texto', 'numero', 'multipla_escolha', 'caixa_selecao'])->default('texto');
            $table->foreignIdFor(Formulario::class)->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('perguntas_formularios');
    }
}
