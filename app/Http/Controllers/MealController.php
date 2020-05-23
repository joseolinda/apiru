<?php

namespace App\Http\Controllers;

use App\Campus;
use App\Meal;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\User as Authenticatable;

class MealController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    private $rules = [
        'description' => 'required',
        'qtdTimeReservationEnd' => 'required',
        'qtdTimeReservationStart' => 'required',
        'timeEnd' => 'required',
        'timeStart' => 'required',
        'campus_id' => 'required',

    ];
    private $messages = [
        'campus_id.required' => 'O Campus é obrigatório',
        'description.required' => 'A descrição é obrigatória',
        'qtdTimeReservationEnd.required' => 'Quantidade de Hora de Inicios é obrigatório',
        'qtdTimeReservationStart.required' => 'Quantidade de Hora de Fim é obrigatório',
        'timeEnd.required' => 'Hora de fim é obrigatório',
        'timeStart.required' => 'Hora de Inicio é obrigatório',
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
    public function index(Request $request)
    {
        $user = auth()->user();
        $description = $request->description;

        $meals = Meal::when($description, function ($query) use ($description) {
            return $query->where('description', 'like', '%'.$description.'%');
        })
        ->where('campus_id', $user->campus_id)
        ->orderBy('description')
        ->paginate(10);

        return response()->json($meals);

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       $meal = new Meal();

        $validation = Validator::make($request->all(),$this->rules,$this->messages);

        if($validation->fails()){
            return $validation->errors()->toJson();
        }

        //Verificando se existe campus cadastrad.
        if(!$this->verifyCampusValid($request->campus_id)){
            return response()->json([
                'message' => 'Campus inválido!'
            ], 404);
        }

        $meal->description = $request->description;
        $meal->campus_id = $request->campus_id;
        $meal->qtdTimeReservationEnd = $request->qtdTimeReservationEnd;
        $meal->qtdTimeReservationStart = $request->qtdTimeReservationStart;
        $meal->timeEnd = $request->timeEnd;
        $meal->timeStart = $request->timeStart;
        $meal->save();

        return response()->json($meal, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $meal = Meal::find($id);
        if (!$meal){
            return response()->json([
                'message' => 'Refeição não encontrada!'
            ], 404);
        }
        return response()->json($meal);

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

        $meal = Meal::find($id);

        if(!$meal){
            return response()->json([
                'message' => 'Refeição não encontrada!'
            ], 404);
        }

        $meal->description = $request->description;
        $meal->campus_id = $request->campus_id;
        $meal->qtdTimeReservationEnd = $request->qtdTimeReservationEnd;
        $meal->qtdTimeReservationStart = $request->qtdTimeReservationStart;
        $meal->timeEnd = $request->timeEnd;
        $meal->timeStart = $request->timeStar;
        $meal->save();

        return response()->json($meal, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $meal = Meal::find($id);
        if (!$meal){
            return response()->json([
                'message' => 'Refeição não encontrada!'
            ], 404);
        }
        $meal->delete();

        return response()->json([
            'message' => 'Operação realizada com sucesso!'
        ], 200);
    }

    public function search($search)
    {
        $meal = Meal::where( 'description', 'LIKE', '%' . $search . '%' )->get();
        if(!$meal){
            return response()->json([
                'message' => 'Refeição não encontrada!'
            ], 404);
        }
        return response()->json($meal, 200);
    }
}
