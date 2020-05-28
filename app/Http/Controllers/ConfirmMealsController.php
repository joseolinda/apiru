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

class ConfirmMealsController extends Controller
{
    public function confirmMeal(Request $request)
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

        $user = auth()->user();

        $menu = Menu::where('meal_id', $request->meal_id)
            ->where('date', $request->date)
            ->where('campus_id', $user->campus_id)
            ->first();
        if(!$menu){
            return response()->json([
                'message' => 'Não existe cárdapio cadastrado para esta data.'
            ], 202);
        }

        $scheduling = Scheduling::where('student_id', $request->student_id)
            ->where('date', $request->date)
            ->where('meal_id', $request->meal_id)
            ->where('campus_id', $user->campus_id)
            ->first();

        if(!$scheduling){
            return response()->json([
                'message' => 'O agendamento não foi encontrado.'
            ], 404);
        }

        if($scheduling->wasPresent == 1){
            return response()->json([
                'message' => 'A refeição já foi confirmada.'
            ], 202);
        }

        $scheduling->wasPresent = 1;
        $scheduling->user_id = $user->id;
        $scheduling->menu_id = $menu->id;
        $scheduling->time = date('H:i:s');;

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
            ->where('wasPresent', 1)
            ->orderBy('date', 'desc')
            ->paginate(10);

        return response()->json($schedulings, 200);

    }

}
