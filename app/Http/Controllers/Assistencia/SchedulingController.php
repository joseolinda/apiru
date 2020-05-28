<?php

namespace App\Http\Controllers\Assistencia;

use App\Campus;
use App\Http\Controllers\Controller;
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

    public function absenceJustification(Request $request, $id){
        $schedule = Scheduling::where('id', $id)->first();

        if(!$schedule){
            return response()->json([
                'message' => 'Agendamento não encontrado.'
            ], 404);
        }

        if(!$request->absenceJustification){
            return response()->json([
                'message' => 'Informe a justificativa!'
            ], 404);
        }

        $user = auth()->user();
        if($schedule->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'O Agendamento pertence a outro campus.'
            ], 202);
        }

        $schedule->absenceJustification = $request->absenceJustification;
        $schedule->save();

        return response()->json($schedule, 200);

    }

    public function scheduleMeal(Request $request)
    {

        if(!$request->student_id){
            return response()->json([
                'message' => 'Informe o estudante'
            ], 202);
        }

        if(!$request->meal_id){
            return response()->json([
                'message' => 'Informe a refeição'
            ], 202);
        }

        if(!$request->date){
            return response()->json([
                'message' => 'Informe a data'
            ], 202);
        }

        if($request->date < date('yy-m-d')){
            return response()->json([
                'message' => 'A data do agendamento não pode ser menor que a data atual.'
            ], 202);
        }

        $user = auth()->user();

        $student = Student::where('id', $request->student_id)
            ->where('campus_id',$user->campus_id)
            ->first();
        if(!$student){
            return response()->json([
                'message' => 'Estudante não encontrado.'
            ], 404);
        }

        $meal = Meal::where('id', $request->meal_id)
            ->where('campus_id',$user->campus_id)
            ->first();
        if(!$meal){
            return response()->json([
                'message' => 'Refeição não encontrada.'
            ], 404);
        }

        $menu = Menu::where('meal_id', $meal->id)
            ->where('date', $request->date)
            ->where('campus_id', $user->campus_id)
            ->first();
        if(!$menu){
            return response()->json([
                'message' => 'Não existe cárdapio cadastrado para esta data.'
            ], 202);
        }

        $schedulingStudent = Scheduling::where('wasPresent', 0)
            ->where('absenceJustification', null)
            ->where('student_id', $student->id)
            ->where('campus_id', $user->campus_id)
            ->where('canceled_by_student', 0)
            ->where('date', '<',  $request->date)
            ->get();
        if(sizeof($schedulingStudent)>0){
            return response()->json([
                'message' => 'O estudante está bloqueado.'
            ], 404);
        }

        $schedulingVerifyDuplicated = Scheduling::where('date', $request->date)
            ->where('student_id', $student->id)
            ->where('meal_id', $meal->id)
            ->where('campus_id', $user->campus_id)
            ->where('canceled_by_student', 0)
            ->get();
        if(sizeof($schedulingVerifyDuplicated)>0){
            return response()->json([
                'message' => 'A refeição já foi cadastrada para o estudante.'
            ], 404);
        }

        $scheduling = new Scheduling();
        $scheduling->wasPresent = 0;
        $scheduling->date = $request->date;
        $scheduling->dateInsert = date('yy-m-d');
        $scheduling->menu_id = $menu->id;
        $scheduling->meal_id = $meal->id;
        $scheduling->student_id = $student->id;
        $scheduling->campus_id = $user->campus_id;

        $scheduling->save();

        return response()->json($scheduling, 200);

    }
}
