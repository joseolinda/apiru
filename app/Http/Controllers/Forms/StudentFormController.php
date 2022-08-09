<?php

namespace App\Http\Controllers\Forms;

use App\Formulario;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\PerguntasFormulario;
use App\RespostaFormulario;
use App\User;

class StudentFormController extends Controller
{

    public function __construct(Request $request)
    {
        $this->middleware('jwt.auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if( $form_active = $this->hasFormActive($user) ) {
            $wasResponded = $this->formWasResponded($form_active->id, $user->id);
            return response()->json([
                "showForm" => !$wasResponded,
                "form" => $form_active
            ], 202);
        } else {
            return response()->json(["showForm" => false], 202);
        }
    }

    /**
     * Mostra se tem formulário ativo.
     *
     * @return Formulario instace or false
     */
    public function hasFormActive(User $user)
    {

        $form_active = Formulario::where("status_form", "publicado")
        ->where('campus_id', $user->campus_id)
        ->orderBy('id', 'desc')
        ->first();
                       
        return $form_active ? $form_active : false;
    }

    /**
     * Mostra se tem formulário foi respondido.
     *
     * @return boolean
     */
    public function formWasResponded(int $form_id, int $user_id)
    {

        $response = RespostaFormulario::where("user_id", $user_id)
        ->where("form_id", $form_id)
        ->first();

        return !!$response;
    }

    /**
     * Mostra se tem formulário foi respondido.
     *
     * @return boolean
     */
    public function formQuestions(int $form_id)
    {

        $questions = PerguntasFormulario::where("form_id", $form_id);

        return $questions;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }
}
