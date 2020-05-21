<?php

namespace App\Http\Controllers\Assistencia;

use App\Campus;
use App\Course;
use App\Http\Controllers\Controller;
use App\Itensrepublic;
use App\Republic;
use App\Scheduling;
use App\Shift;
use App\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\User as Authenticatable;

class RepublicController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    private $rules = [
        'description' => 'required',
        'neighborhood' => 'required',
        'ownerRepublic' => 'required',
        'valueRepublic' => 'required',
        'city' => 'required',
        'address' => 'required',
    ];
    private $messages = [
        'description.required' => 'A descrição é obrigatória',
        'neighborhood.required' => 'O bairro é obrigatório',
        'ownerRepublic.required' => 'O proprietário da república é obrigatório',
        'valueRepublic.required' => 'O valor do aluguel é obrigatório',
        'city.required' => 'A cidade da república é obrigatória',
        'address.required' => 'O endereço é obrigatório',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $description = $request->description;

        $republic = Republic::when($description, function ($query) use ($description) {
                return $query->where('description', 'like', '%'.$description.'%');
            })
            ->where('campus_id', $user->campus_id)
            ->with('itensrepublics')
            ->orderBy('description')
            ->paginate(10);

        return response()->json($republic);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(),$this->rules,$this->messages);

        if($validation->fails()){
            $erros = array('errors' => array(
                $validation->messages()
            ));
            $json_str = json_encode($erros);
            return response($json_str, 202);
        }


        $user = auth()->user();

        $republic = new Republic();
        $republic->description = $request->description;
        $republic->city = $request->city;
        $republic->address = $request->address;
        $republic->neighborhood = $request->neighborhood;
        $republic->ownerRepublic = $request->ownerRepublic;
        $republic->valueRepublic = $request->valueRepublic;
        $republic->campus_id = $user->campus_id;
        $republic->save();

        return response()->json(
            $republic, 200);
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
                'message' => 'República não encontrada!'
            ], 404);
        }

        $user = auth()->user();
        if($republic->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'O república pertence a outro campus.'
            ], 202);
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
            $erros = array('errors' => array(
                $validation->messages()
            ));
            $json_str = json_encode($erros);
            return response($json_str, 202);
        }

        $republic = Republic::find($id);

        if(!$republic){
            return response()->json([
                'message' => 'República não encontrada!'
            ], 404);
        }

        $user = auth()->user();
        if($republic->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'A república pertence a outro campus.'
            ], 202);
        }

        $republic->description = $request->description;
        $republic->city = $request->city;
        $republic->address = $request->address;
        $republic->neighborhood = $request->neighborhood;
        $republic->ownerRepublic = $request->ownerRepublic;
        $republic->valueRepublic = $request->valueRepublic;
        $republic->campus_id = $user->campus_id;
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
                'message' => 'República não encontrada!'
            ], 404);
        }

        $user = auth()->user();
        if($republic->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'A república pertence a outro campus.'
            ], 202);
        }

        $itensRepublic = Itensrepublic::where('republic_id', $republic->id)->get();
        if(sizeof($itensRepublic)>0){
            return response()->json([
                'message' => 'A república possui estudantes associados.'
            ], 202);
        }

        $republic->delete();

        return response()->json([
            'message' => 'Operação realizada com sucesso!'
        ], 200);
    }
}
