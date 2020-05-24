<?php

namespace App\Http\Controllers\Assistencia;

use App\Allowstudenmealday;
use App\Campus;
use App\Http\Controllers\Controller;
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
        //verifica se o estudante é do mesmo campus do usuário que está cadastrando
        $user = auth()->user();
        if($user->campus_id != $student->campus_id){
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
        //verifica se o refeição é do mesmo campus do usuário que está cadastrando
        $user = auth()->user();
        if($user->campus_id != $meal->campus_id){
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
        if(!$request->student_id){
            return response()->json([
                'message' => 'Informe o estudante.'
            ], 404);
        }
        $user = auth()->user();
        $student = Student::where('id', $request->student_id)->first();
        if(!$student){
            return response()->json([
                'message' => 'Estudante não existe.'
            ], 404);
        }
        if($user->campus_id != $student->campus_id){
            return response()->json([
                'message' => 'Permissões faz parte de outro campus!'
            ], 202);
        }
        $allowstudenmealday = Allowstudenmealday::where('student_id', $request->student_id)
            ->with('meal')
            ->with('student')
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($allowstudenmealday, 200);
    }

    public function allMeal(Request $request)
    {
        $user = auth()->user();
        $description = $request->description;

        $meals = Meal::where('campus_id', $user->campus_id)
            ->orderBy('description')
            ->get();

        return response()->json($meals, 200);

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

        if(!$this->verifyStudentValid($request->student_id)){
            return response()->json([
                'message' => 'Estudante inválido!'
            ], 404);
        }

        if(!$this->verifyMealValid($request->meal_id)){
            return response()->json([
                'message' => 'Refeição inválida !'
            ], 404);
        }

        $verify = Allowstudenmealday::where('student_id', $request->student_id)
            ->where('meal_id', $request->meal_id)
            ->get();
        if(sizeof($verify)>0){
            return response()->json([
                'message' => 'Permissão ja cadastrada !'
            ], 202);
        }

        $allowstudenmealday = new Allowstudenmealday();
        $allowstudenmealday->student_id = $request->student_id;
        $allowstudenmealday->meal_id = $request->meal_id;
        $request->friday ? $allowstudenmealday->friday = $request->friday
                                        : $allowstudenmealday->friday = false;
        $request->monday ? $allowstudenmealday->monday = $request->monday
                                        : $allowstudenmealday->monday = false;
        $request->saturday ? $allowstudenmealday->saturday = $request->saturday
                                        : $allowstudenmealday->saturday = false;
        $request->thursday ? $allowstudenmealday->thursday = $request->thursday
                                        : $allowstudenmealday->thursday = false;
        $request->tuesday ? $allowstudenmealday->tuesday = $request->tuesday
                                        : $allowstudenmealday->tuesday = false;
        $request->wednesday ? $allowstudenmealday->wednesday = $request->wednesday
                                        : $allowstudenmealday->wednesday = false;
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
                'message' => 'Permissões não encontrada!'
            ], 404);
        }

        $student = Student::where('id', $allowstudenmealday->student_id)->first();
        $user = auth()->user();
        if($user->campus_id != $student->campus_id){
            return response()->json([
                'message' => 'Permissões faz parte de outro campus!'
            ], 202);
        }

        return response()->json($allowstudenmealday, 200);
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

        $student = Student::where('id', $allowstudenmealday->student_id)->first();
        $user = auth()->user();
        if($user->campus_id != $student->campus_id){
            return response()->json([
                'message' => 'Permissões faz parte de outro campus!'
            ], 202);
        }

        $allowstudenmealday->student_id = $request->student_id;
        $allowstudenmealday->meal_id = $request->meal_id;
        $request->friday ? $allowstudenmealday->friday = $request->friday
            : $allowstudenmealday->friday = false;
        $request->monday ? $allowstudenmealday->monday = $request->monday
            : $allowstudenmealday->monday = false;
        $request->saturday ? $allowstudenmealday->saturday = $request->saturday
            : $allowstudenmealday->saturday = false;
        $request->thursday ? $allowstudenmealday->thursday = $request->thursday
            : $allowstudenmealday->thursday = false;
        $request->tuesday ? $allowstudenmealday->tuesday = $request->tuesday
            : $allowstudenmealday->tuesday = false;
        $request->wednesday ? $allowstudenmealday->wednesday = $request->wednesday
            : $allowstudenmealday->wednesday = false;

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
                'message' => 'Permissão não encontrada!'
            ], 404);
        }

        $student = Student::where('id', $allowstudenmealday->student_id)->first();
        $user = auth()->user();
        if($user->campus_id != $student->campus_id){
            return response()->json([
                'message' => 'Permissões faz parte de outro campus!'
            ], 202);
        }

        $allowstudenmealday->delete();

        return response()->json([
            'message' => 'Operação realizada com sucesso!'
        ], 200);
    }

}
