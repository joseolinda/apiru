<?php

namespace App\Http\Controllers;

use App\Campus;
use App\Meal;
use App\Menu;
use App\Scheduling;
use App\Student;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SchedulingController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    private $rules = [
        'date' => 'required',
        'dateInsert' => 'required',
        'time' => 'required',
        'campus_id' => 'required',
        'meal_id' => 'required',
        'menu_id' => 'required',
        'student_id' => 'required',
        'user_id' => 'required',
    ];
    private $messages = [
        'date.required' => 'A Data é obrigatória',
        'dateInsert' => 'A Data de criação é obrigatória',
        'time' => 'O horário é obrigatório',
        'campus_id.required' => 'O Campus é obrigatório',
        'meal_id.required' => 'A refeição é obrigatória',
        'menu_id.required' => 'O Cardápio é obrigatório',
        'student_id.required' => 'Os estudantes é obrigatória',
        'user_id.required' => 'O usuário é obrigatória',
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
    public function verifyMealValid($id){
        if(empty($id)) {
            return false;
        }
        $campus = Meal::find($id);
        if(!$campus){
            return false;
        }
        return true;
    }

    public function verifyMenuValid($id){
        if(empty($id)) {
            return false;
        }
        $campus = Menu::find($id);
        if(!$campus){
            return false;
        }
        return true;
    }

    public function verifyStudentValid($id){
        if(empty($id)) {
            return false;
        }
        $campus = Student::find($id);
        if(!$campus){
            return false;
        }
        return true;
    }

    public function verifyUserValid($id){
        if(empty($id)) {
            return false;
        }
        $campus = User::find($id);
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
        $schedulings = Scheduling::get();
        return response()->json($schedulings);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $scheduling = new Scheduling();

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
        if(!$this->verifyMealValid($request->meal_id)){
            return response()->json([
                'message' => 'Refeição inválida!'
            ], 404);
        }
        if(!$this->verifyMenuValid($request->menu_id)){
            return response()->json([
                'message' => 'Cardápio inválido!'
            ], 404);
        }
        if(!$this->verifyStudentValid($request->student_id)){
            return response()->json([
                'message' => 'Estudante inválido!'
            ], 404);
        }
        if(!$this->verifyUserValid($request->user_id)){
            return response()->json([
                'message' => 'Usuário inválido!'
            ], 404);
        }

        $scheduling->absenceJustification = $request->absenceJustification;
        $scheduling->canceled_by_student = $request->canceled_by_student;
        $scheduling->date = $request->date;
        $scheduling->dateInsert = $request->dateInsert;
        $scheduling->ticketCode = $request->ticketCode;
        $scheduling->time = $request->time;
        $scheduling->wasPresent = $request->wasPresent;
        $scheduling->campus_id = $request->campus_id;
        $scheduling->meal_id = $request->meal_id;
        $scheduling->menu_id = $request->menu_id;
        $scheduling->student_id = $request->student_id;
        $scheduling->user_id = $request->user_id;

        $scheduling->save();

        return response()->json($scheduling, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $scheduling = Scheduling::find($id);
        if (!$scheduling){
            return response()->json([
                'message' => 'Refeição não encontrado!'
            ], 404);
        }
        return response()->json($scheduling);
    }

    /**
     * canceled_by_student the Scheduling
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function canceledScheduling(Request $request, $id){
        $scheduling = Scheduling::find($id);
        //Falta validar se pode cancelar antes do dia e hrs estabelecidos.
        if(!$scheduling){
            return response()->json([
                'message' => 'Agendamento não encontrado!'
            ], 404);
        }

        $d = date("d-m-Y | h:i:s");
        strtotime($d);
        $scheduling->absenceJustification = "O estudante cancelou a solicitação em:".$d.'';
        $scheduling->canceled_by_student = true;

        $scheduling->save();

        return response()->json([
            'message' => 'Operação realizada com sucesso!'
        ], 200);
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

        $scheduling = Scheduling::find($id);

        if(!$scheduling){
            return response()->json([
                'message' => 'Agendamento não encontrado!'
            ], 404);
        }

        $scheduling->absenceJustification = $request->absenceJustification;
        $scheduling->canceled_by_student = $request->canceled_by_student;
        $scheduling->date = $request->date;
        $scheduling->dateInsert = $request->dateInsert;
        $scheduling->ticketCode = $request->ticketCode;
        $scheduling->time = $request->time;
        $scheduling->wasPresent = $request->wasPresent;
        $scheduling->campus_id = $request->campus_id;
        $scheduling->meal_id = $request->meal_id;
        $scheduling->menu_id = $request->menu_id;
        $scheduling->student_id = $request->student_id;
        $scheduling->user_id = $request->user_id;

        $scheduling->save();

        return response()->json($scheduling, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $scheduling = Scheduling::find($id);
        if (!$scheduling){
            return response()->json([
                'message' => 'Refeição não encontrado!'
            ], 404);
        }
        $scheduling->delete();

        return response()->json([
            'message' => 'Operação realizada com sucesso!'
        ], 200);
    }

    //Analisar se o comando está certo. Need to correct.

    public function search($search)
    {
        $student = Student::where('id',$search)->orWhere('mat', '=',$search)->first();
        $scheduling = Scheduling::where('user_id',$student->id)->get();
        if(!$scheduling){
            return response()->json([
                'message' => 'Agendamento não encontrado!'
            ], 404);
        }
        return response()->json($scheduling, 200);
    }
}
