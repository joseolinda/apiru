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
        'student_id.required' => 'O estudante é obrigatória',
        'user_id.required' => 'O usuário é obrigatória',
    ];

    public function absenceJustification(Request $request, $id){
        $schedule = Scheduling::where('id', $id)->first();

        if(!$schedule){
            return response()->json([
                'message' => 'O Agendamento não foi encontrado.'
            ], 202);
        }

        if(!$request->absenceJustification){
            return response()->json([
                'message' => 'A justificativa não foi informada.'
            ], 202);
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
                'message' => 'O estudante não foi informado.'
            ], 202);
        }

        if(!$request->meal_id){
            return response()->json([
                'message' => 'A refeição não foi informada.'
            ], 202);
        }

        if(!$request->date){
            return response()->json([
                'message' => 'A data não foi informada.'
            ], 202);
        }

        $user = auth()->user();

        $student = Student::where('id', $request->student_id)
            ->where('campus_id',$user->campus_id)
            ->first();
        if(!$student){
            return response()->json([
                'message' => 'O estudante não foi encontrado..'
            ], 202);
        }

        $meal = Meal::where('id', $request->meal_id)
            ->where('campus_id',$user->campus_id)
            ->first();
        if(!$meal){
            return response()->json([
                'message' => 'A Refeição não foi encontrada.'
            ], 202);
        }

        $menu = Menu::where('meal_id', $meal->id)
            ->where('date', $request->date)
            ->where('campus_id', $user->campus_id)
            ->first();
        if(!$menu){
            return response()->json([
                'message' => 'Não existe cardápio cadastrado para esta data.'
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
                'message' => 'O estudante esteve ausente em alguma refeição. É necessário justificá-la.'
            ], 202);
        }

        $schedulingVerifyDuplicated = Scheduling::where('date', $request->date)
            ->where('student_id', $student->id)
            ->where('meal_id', $meal->id)
            ->where('campus_id', $user->campus_id)
            ->where('canceled_by_student', 0)
            ->get();
        
        if(sizeof($schedulingVerifyDuplicated)>=1){
            return response()->json([
                'message' => 'A refeição já foi cadastrada para o estudante.'
            ], 202);
        }

        date_default_timezone_set('America/Sao_Paulo');
        $scheduling = new Scheduling();
        $scheduling->wasPresent = 0;
        $scheduling->date = $request->date;
        $scheduling->dateInsert = date('Y-m-d');
        $scheduling->menu_id = $menu->id;
        $scheduling->meal_id = $meal->id;
        $scheduling->student_id = $student->id;
        $scheduling->campus_id = $user->campus_id;

        $scheduling->save();

        return response()->json($scheduling, 200);

    }

    public function listSchedulingMeals(Request $request)
    {
        if(!$request->date){
            return response()->json([
                'message' => 'A data não foi informada.'
            ], 202);
        }

        $user = auth()->user();

        $schedulings = Scheduling::where('date', $request->date)
            ->where('campus_id', $user->campus_id)
            ->where('wasPresent', 0)
            ->with('student')
            ->with('menu')
            ->with('meal')
            ->orderBy('date', 'desc')
            ->paginate(10);

        return response()->json($schedulings, 200);

    }

    public function destroy($id)
    {
        $Scheduling = Scheduling::find($id);
        if (!$Scheduling){
            return response()->json([
                'message' => 'O Agendamento não foi encontrado'
            ], 202);
        }
        $user = auth()->user();
        if($Scheduling->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'O Agendamento pertence a outro campus.'
            ], 202);
        }
        $Scheduling->delete();
        return response()->json([
            'message' => 'O Agendamento foi excluído.'
        ], 200);
    }
}
