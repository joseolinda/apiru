<?php

namespace App\Http\Controllers;

use App\Allowstudenmealday;
use App\Campus;
use App\Meal;
use App\Menu;
use App\Scheduling;
use App\Student;
use App\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Validator;
use JWTAuth;

class All extends Controller
{

    public function menusToday(Request $request)
    {
        //verifica quais refeições estão habilitadas para fazer reserva no horário solicitado.
        $meals = Meal::all();
        $resultMealsEnable = array();
        foreach ($meals as $meal){

            $dataEnd = new \DateTime( $request->date .' '. $meal->timeEnd);
            $dataEnd->sub(new \DateInterval('PT'.$meal->qtdTimeReservationEnd.'H'));

            $dateNow = new \DateTime();

            $auxAnswer= (object)[
                //'dtStart' => $dataStart,
                'dtEnd' => $dataEnd,
                'meal' => $meal,

            ];
            if($dataEnd > $dateNow){
                $resultMealsEnable[] = $meal->id;
            }

        }

        $user = auth()->user();

        $menu = Menu::where('date',\date('Y-m-d'))
            //->where('campus_id', $user->campus_id)
            ->whereIn('meal_id', $resultMealsEnable)
            ->with('meal')
            ->orderBy('description')
            ->get();

        return response()->json($menu, 200);

    }

    public function menusByWeek(Request $request)
    {

        $user = auth()->user();

        $menu = Menu::where('date', '>=',\date('Y-m-d'))
            ->where('date', '<=', \date('Y-m-d', strtotime('+7 days')))
            ->where('campus_id', $user->campus_id)
            ->with('meal')
            ->orderBy('date')
            ->get();

        return response()->json($menu, 200);

    }

    public function allMenuByDay(Request $request)
    {
        if(!$request->date){
            if(!$request->mat){
                return response()->json([
                    'message' => 'Informe a data.'
                ], 202);
            }
        }

        $user = auth()->user();

        $menu = Menu::where('date', $request->date)
            ->where('campus_id', $user->campus_id)
            ->with('meal')
            ->orderBy('description')
            ->get();

        return response()->json($menu, 200);

    }

    public function allMeal()
    {
        $user = auth()->user();

        $meals = Meal::where('campus_id', $user->campus_id)
            ->where('campus_id', $user->campus_id)
            ->orderBy('description')
            ->get();

        return response()->json($meals, 200);

    }

    public function allStudent()
    {
        $user = auth()->user();

        $students = Student::where('campus_id', $user->campus_id)
            ->orderBy('name')
            ->get();

        return response()->json($students, 200);

    }


    public function showStudent($id)
    {

        $user = User::where('id', $id)->first();
        if (!$user){
            return response()->json([
                'message' => 'Estudante não encontrado!'
            ], 404);
        }

        if (!$user->student_id){
            return response()->json([
                'message' => 'Estudante não encontrado!'
            ], 202);
        }

        $student = Student::where('id', $user->student_id) ->with('course')->first();

        if ($user->campus_id!=$student->campus_id){
            return response()->json([
                'message' => 'Estudante não pertence a esse campus!'
            ], 404);
        }
        return response()->json($student, 200);

    }

    public function showUser($id)
    {
        $user = User::where('id', $id)->first();
        if (!$user){
            return response()->json([
                'message' => 'Estudante não encontrado!'
            ], 404);
        }

        if (!$user->student_id){
            return response()->json([
                'message' => 'Estudante não encontrado!'
            ], 202);
        }

        return response()->json($user, 200);
    }

    public function studentByMatOrCod(Request $request)
    {
        $user = auth()->user();

        if(!$request->mat){
            return response()->json([
                'message' => 'Informe a matrícula ou o código do estuante.'
            ], 202);
        }

        $student = Student::where('id', $request->mat)
            ->orWhere('mat', $request->mat)
            ->orderBy('name')
            ->first();

        if($student->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'O estudante pertence a outro campus.'
            ], 202);
        }

        if($student->active == 0){
            return response()->json([
                'message' => 'O estudante está inativo.'
            ], 202);
        }

        if($student->dateValid < date('yy-m-d')){
            return response()->json([
                'message' => 'O estudante precisa fazer a atualização cadastral.'
            ], 202);
        }

        $schedulingStudent = Scheduling::where('wasPresent', 0)
            ->where('absenceJustification', null)
            ->where('student_id', $student->id)
            ->where('campus_id', $user->campus_id)
            ->where('date', '<', date('yy-m-d'))
            ->where('canceled_by_student', 0)
            ->get();

        if(sizeof($schedulingStudent)>0){
            return response()->json([
                'message' => 'O estudante está bloqueado.'
            ], 202);
        }

        return response()->json($student, 200);

    }

}
