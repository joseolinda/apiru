<?php

namespace App\Http\Controllers\Admin;

use App\Campus;
use App\Http\Controllers\Controller;
use App\User;
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
        'description.required' => 'A DESCRIÇÃO é obrigatória',
    ];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $description = $request->description;
        $campus = Campus::when($description, function ($query) use ($description) {

                return $query->where('description', 'like', '%'.$description.'%');
            })
            ->orderBy('description')
            ->paginate(10);
        return response()->json($campus,200);
    }

    public function all(Request $request)
    {
        $description = $request->description;
        $campus = Campus::all();
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
            $erros = array('errors' => array(
                $validation->messages()
            ));
            $json_str = json_encode($erros);
            return response($json_str, 202);
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
            $erros = array('errors' => array(
                $validation->messages()
            ));
            $json_str = json_encode($erros);
            return response($json_str, 202);
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
        $user = User::where('campus_id', $campus->id)->get();

        if(sizeof($user)>0){
            return response()->json([
                'message' => 'Existem usuários cadastrados para o campus.'
            ], 202);
        }

        $campus->delete();

        return response()->json([
            'message' => 'Campus deletado.'
        ], 200);
    }
}
