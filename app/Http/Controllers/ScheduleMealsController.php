<?php

namespace App\Http\Controllers;

use App\Allowstudenmealday;
use App\Campus;
use App\Meal;
use App\Menu;
use App\Scheduling;
use App\Student;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Validator;
use JWTAuth;

class ScheduleMealsController extends Controller
{
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
            ->get();
        if(sizeof($schedulingStudent)>0){
            return response()->json([
                'message' => 'O estudante está bloqueado.'
            ], 404);
        }

        $scheduling = new Scheduling();
        $scheduling->wasPresent = 0;
        $scheduling->dateInsert = date('yy-m-d');
        $scheduling->menu_id = $menu->id;
        $scheduling->meal_id = $meal->id;
        $scheduling->student_id = $student->id;
        $scheduling->campus_id = $user->campus_id;

        $scheduling->save();

        return response()->json($scheduling, 200);

    }

    public function listConfirmedMeals(Request $request)
    {
        if(!$request->date){
            return response()->json([
                'message' => 'Informe a data.'
            ], 202);
        }

        $user = auth()->user();

        $schedulings = Scheduling::where('date', $request->date)
            ->where('campus_id', $user->campus_id)
            ->orderBy('date', 'desc')
            ->paginate(10);

        return response()->json($schedulings, 200);

    }

}
