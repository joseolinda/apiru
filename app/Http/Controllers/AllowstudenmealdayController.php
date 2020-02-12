<?php

namespace App\Http\Controllers;

use App\Allowstudenmealday;
use App\Campus;
use App\Meal;
use App\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\StaticAnalysis\HappyPath\AssertNotInstanceOf\A;

class AllowstudenmealdayController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    private $rules = [
        'student_id' => 'required',
        'meal_id' => 'required',
    ];
    private $messages = [
        'student_id.required' => 'O Estudante é obrigatório',
        'meal_id.required' => 'A refeição é obrigatória',
    ];

    public function verifyStudentValid($id){
        if(empty($id)) {
            return false;
        }
        $student = Student::find($id);
        if(!$student){
            return false;
        }
        return true;
    }

    public function verifyMealValid($id){
        if(empty($id)) {
            return false;
        }
        $meal = Meal::find($id);
        if(!$meal){
            return false;
        }
        return true;
    }

    public function verifyStudentDouble($id){
        if(empty($id)) {
            return false;
        }
        $studentDouble = Allowstudenmealday::get();
        foreach($studentDouble as $x){
            if($x->student_id == $id){
                return false;
                break;
            }
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
        $allowstudenmealday = Allowstudenmealday::get();
        return response()->json($allowstudenmealday, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $allowstudenmealday = new Allowstudenmealday();

        $validation = Validator::make($request->all(),$this->rules,$this->messages);

        if($validation->fails()){
            return $validation->errors()->toJson();
        }

        //Verificando se existe campus cadastrados.
        if(!$this->verifyStudentValid($request->student_id)){
            return response()->json([
                'message' => 'Estudante inválido!'
            ], 404);
        }
        //Verificando se existe refeição cadastrados.
        if(!$this->verifyMealValid($request->meal_id)){
            return response()->json([
                'message' => 'Refeição inválida !'
            ], 404);
        }
        //Verificando se existe deplicidade de estudantes.
        if(!$this->verifyStudentDouble($request->student_id)){
            return response()->json([
                'message' => 'Estudante ja cadastrado !'
            ], 404);
        }

        $allowstudenmealday->student_id = $request->student_id;
        $allowstudenmealday->meal_id = $request->meal_id;
        $allowstudenmealday->friday = $request->friday;
        $allowstudenmealday->monday = $request->monday;
        $allowstudenmealday->saturday = $request->saturday;
        $allowstudenmealday->thursday = $request->thursday;
        $allowstudenmealday->tuesday = $request->tuesday;
        $allowstudenmealday->wednesday = $request->wednesday;
        $allowstudenmealday->save();

        return response()->json($allowstudenmealday, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $allowstudenmealday = Allowstudenmealday::find($id);
        if (!$allowstudenmealday){
            return response()->json([
                'message' => 'Permições não encontrada!'
            ], 404);
        }
        return response()->json($allowstudenmealday);
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

        $allowstudenmealday = Allowstudenmealday::find($id);

        if (!$allowstudenmealday){
            return response()->json([
                'message' => 'Permição não encontrada!'
            ], 404);
        }

        $allowstudenmealday->student_id = $request->student_id;
        $allowstudenmealday->meal_id = $request->meal_id;
        $allowstudenmealday->friday = $request->friday;
        $allowstudenmealday->monday = $request->monday;
        $allowstudenmealday->saturday = $request->saturday;
        $allowstudenmealday->thursday = $request->thursday;
        $allowstudenmealday->tuesday = $request->tuesday;
        $allowstudenmealday->wednesday = $request->wednesday;
        $allowstudenmealday->save();

        return response()->json($allowstudenmealday, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $allowstudenmealday = Allowstudenmealday::find($id);

        if (!$allowstudenmealday){
            return response()->json([
                'message' => 'Permição não encontrada!'
            ], 404);
        }

        $allowstudenmealday->delete();

        return response()->json([
            'message' => 'Operação realizada com sucesso!'
        ], 200);
    }

    //Analisar se está correto.
    public function search($search)
    {
        $allowstudenmealday = Allowstudenmealday::where( 'student_id', 'LIKE', '%' . $search . '%' )->get();
        if(!$allowstudenmealday){
            return response()->json([
                'message' => 'Dados não encontrados!'
            ], 404);
        }
        return response()->json($allowstudenmealday, 200);
    }
}
