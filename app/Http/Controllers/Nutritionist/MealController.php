<?php

namespace App\Http\Controllers\Nutritionist;

use App\Campus;
use App\Meal;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    ];
    private $messages = [
        'description.required' => 'A descrição é obrigatória',
        'qtdTimeReservationEnd.required' => 'A quantidade de horas do fim da reserva antes do horário da refeição deve ser informada',
        'qtdTimeReservationStart.required' => 'A quantidade de horas do inicio da reserva antes do horário da refeição deve ser informada',
        'timeEnd.required' => 'Hora de fim da refeição é obrigatório',
        'timeStart.required' => 'Hora de inicio da refeição é obrigatório',
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

        $meals = Meal::when($description, function ($query) use ($description) {
            return $query->where('description', 'like', '%'.$description.'%');
        })
        ->where('campus_id', $user->campus_id)
        ->orderBy('description')
        ->paginate(10);

        return response()->json($meals, 200);

    }

    public function all(Request $request)
    {
        $user = auth()->user();
        $meal = Meal::where('campus_id', $user->campus_id)->get();
        return response()->json($meal,200);
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
            return $validation->errors()->toJson();
        }

        if($request->timeStart > $request->timeEnd){
            return response()->json([
                'message' => 'A hora de inicio deve ser menor que hora de fim da refeição'
            ], 202);
        }

        $user = auth()->user();

        $meal = new Meal();
        $meal->description = $request->description;
        $meal->campus_id = $user->campus_id;
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

        $user = auth()->user();
        if ($meal->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'A refeição não pertence ao campus do usuário!'
            ], 202);
        }
        return response()->json($meal, 200);

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

        $user = auth()->user();
        if ($meal->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'A refeição não pertence ao campus do usuário!'
            ], 202);
        }

        if($request->timeStart > $request->timeEnd){
            return response()->json([
                'message' => 'A hora de inicio deve ser menor que hora de fim da refeição'
            ], 202);
        }

        $meal->description = $request->description;
        $meal->campus_id = $user->campus_id;
        $meal->qtdTimeReservationEnd = $request->qtdTimeReservationEnd;
        $meal->qtdTimeReservationStart = $request->qtdTimeReservationStart;
        $meal->timeEnd = $request->timeEnd;
        $meal->timeStart = $request->timeStart;
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

        $user = auth()->user();
        if ($meal->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'A refeição não pertence ao campus do usuário!'
            ], 202);
        }

        $meal->delete();

        return response()->json([
            'message' => 'Operação realizada com sucesso!'
        ], 200);
    }
}
