<?php

namespace App\Http\Controllers;

use App\Campus;
use App\Republic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RepublicController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    private $rules = [
        'description' => 'required',
        'address' => 'required',
        'city' => 'required',
        'neighborhood' => 'required',
        'campus_id' => 'required',
    ];
    private $messages = [
        'campus_id.required' => 'O Campus é obrigatório',
        'description.required' => 'A descrição é obrigatória',
        'address.required' => 'O endereço é obrigatório',
        'neighborhood.required' => 'O bairro é obrigatório',
        'city.required' => 'A cidade é obrigatório',
    ];

    //Métodos Criados
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
        $republics = Republic::get();
        return response()->json($republics);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $republic = new Republic();

        $validation = Validator::make($request->all(),$this->rules,$this->messages);

        if($validation->fails()){
            return $validation->errors()->toJson();
        }

        //Verificando se existe campus cadastrados.
        if(!$this->verifyCampusValid($request->campus_id)){
            return response()->json([
                'message' => 'Campus inválido!'
            ], 404);
        }

        $republic->description = $request->description;
        $republic->address = $request->address;
        $republic->city = $request->city;
        $republic->neighborhood = $request->neighborhood;
        $republic->ownerRepublic = $request->ownerRepublic;
        $republic->valueRepublic = $request->valueRepublic;
        $republic->campus_id = $request->campus_id;
        $republic->save();

        return response()->json($republic, 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $republic = Republic::find($id);
        if (!$republic){
            return response()->json([
                'message' => 'Republica não encontrado!'
            ], 404);
        }
        return response()->json($republic);
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

        $republic = Republic::find($id);

        if(!$republic){
            return response()->json([
                'message' => 'Republica não encontrada!'
            ], 404);
        }

        $republic->description = $request->description;
        $republic->address = $request->address;
        $republic->city = $request->city;
        $republic->neighborhood = $request->neighborhood;
        $republic->ownerRepublic = $request->ownerRepublic;
        $republic->valueRepublic = $request->valueRepublic;
        $republic->campus_id = $request->campus_id;
        $republic->save();

        return response()->json($republic, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $republic = Republic::find($id);
        if (!$republic){
            return response()->json([
                'message' => 'Republica não encontrado!'
            ], 404);
        }
       $republic->delete();

        return response()->json([
            'message' => 'Operação realizada com sucesso!'
        ], 200);
    }

    public function search($search)
    {
        $republic = Republic::where( 'description', 'LIKE', '%' . $search . '%' )->get();
        if(!$republic){
            return response()->json([
                'message' => 'republic não encontrada!'
            ], 404);
        }
        return response()->json($republic, 200);
    }
}
