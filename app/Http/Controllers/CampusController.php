<?php

namespace App\Http\Controllers;

use App\Campus;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CampusController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    private $rules = [
        'description' => 'required',
    ];
    private $messages = [
        'description.required' => 'O Campus é obrigatório',
    ];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $campus = Campus::get();
        return response()->json($campus,200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $campus = new Campus();

        $validation = Validator::make($request->all(),$this->rules,$this->messages);

        if($validation->fails()){
            return $validation->errors()->toJson();
        }

        $campus->description = $request->description;
        $campus->save();

        return response()->json($campus, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $campus = Campus::find($id);
        if(!$campus){
            return response()->json([
                'message' => 'Campus não encontrado!'
            ], 404);
        }
        return response()->json($campus,200);
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

        $campus = Campus::find($id);

        if(!$campus){
            return response()->json([
                'message' => 'Campus não encontrado!'
            ], 404);
        }

        $campus->description = $request->description;
        $campus->save();

        return response()->json($campus, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $campus = Campus::find($id);
        if(!$campus){
            return response()->json([
                'message' => 'Campus não encontrado!'
            ], 404);
        }
        $campus->delete();

        return response()->json([
            'message' => 'Operação realizada com sucesso!'
        ], 200);
    }

    public function search($search)
    {
        $campus = Campus::where( 'description', 'LIKE', '%' . $search . '%' )->get();

        if(!$campus){
            return response()->json([
                'message' => 'Campus não encontrado!'
            ], 404);
        }

        return response()->json($campus,200);
    }
}
