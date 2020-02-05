<?php

namespace App\Http\Controllers;

use App\Campus;
use App\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShiftController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    private $rules = [
        'campus_id' => 'required',
        'description' => 'required',
    ];
    private $messages = [
        'campus_id.required' => 'O Campus é obrigatório',
        'description.required' => 'A descrição é obrigatória',
    ];

    public function verifyCampusValid($id){
        if(empty($id)) {
            return false;
        }
        $campus = Campus::find($id);
        if(!$campus){
            return false;
        }
        return true;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $shifts = Shift::get();
        return response()->json($shifts, 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $shift = new Shift();

        $validation = Validator::make($request->all(),$this->rules,$this->messages);

        if($validation->fails()){
            return $validation->errors()->toJson();
        }

        //Verificando se existe campus cadastrados.
        if(!$this->verifyCampusValid($request->campus_id)){
            return response()->json([
                'message' => 'Campus inválido !'
            ], 404);
        }

        $shift->description = $request->description;
        $shift->campus_id = $request->campus_id;
        $shift->save();

        return response()->json($shift, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $shifts = Shift::find($id);
        if(!$shifts){
            return response()->json([
                'message' => 'Turno não encontrado!'
            ], 404);
        }
        return response()->json($shifts);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make($request->all(),$this->rules,$this->messages);

        if($validation->fails()){
            return $validation->errors()->toJson();
        }

        $shift = Shift::find($id);

        if(!$shift){
            return response()->json([
                'message' => 'Turno não encontrado!'
            ], 404);
        }

        $shift->description = $request->description;
        $shift->campus_id = $request->campus_id;
        $shift->save();

        return response()->json($shift, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $shifts = Shift::find($id);
        if(!$shifts){
            return response()->json([
                'message' => 'Turno não encontrado!'
            ], 404);
        }
        $shifts->delete();

        return response()->json([
            'message' => 'Operação realizada com sucesso!'
        ], 200);
    }

    public function search($search)
    {
        $shift = Shift::where( 'description', 'LIKE', '%' . $search . '%' )->get();
        if(!$shift){
            return response()->json([
                'message' => 'Usuário não encontrado!'
            ], 404);
        }
        return response()->json($shift, 200);
    }
}
