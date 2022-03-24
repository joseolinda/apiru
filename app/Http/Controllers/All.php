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
use mysql_xdevapi\Exception;
use Validator;
use JWTAuth;

class All extends Controller
{

    public function campus_active(Request $request){
        $user = auth()->user();

        $campus = Campus::where('id', $user->campus_id)->first();

        return response()->json($campus, 200);
    }

    public function menusToday(Request $request)
    {
        //verifica quais refeições estão habilitadas para fazer reserva no horário solicitado.
        $meals = Meal::all();
        $resultMealsEnable = array();

        foreach ($meals as $meal){

            $dataStart = new \DateTime( $request->date .' '. $meal->timeStart);
            $dataStart->sub(new \DateInterval('PT'.$meal->qtdTimeReservationStart.'H'));

            $dataEnd = new \DateTime( $request->date .' '. $meal->timeEnd);
            $dataEnd->sub(new \DateInterval('PT'.$meal->qtdTimeReservationEnd.'H'));

            $dateNow = new \DateTime();

            if(($dataStart <= $dateNow) && ($dataEnd >= $dateNow)){
                $resultMealsEnable[] = $meal->id;
            }
        }

        $user = auth()->user();

        //pega as refeições já solicitadas pelo usuário
        $scheduling = Scheduling::where('date',$request->date)
            ->where('student_id', $user->student_id)
            ->pluck('meal_id');

        $menu = Menu::where('date',$request->date)
            //->where('campus_id', $user->campus_id)
            ->whereIn('meal_id', $resultMealsEnable)
            //->whereNotIn('meal_id', $scheduling)
            ->with('meal')
            ->orderBy('meal_id')
            ->get();

        //verifica se o estudante possui permissão e acrescenta um atributo permission ao response
        //pega o dia da semana 0 - Domingo ... 6 - Sábado
        $dayWeek = date('w', strtotime($request->date));
        for($i = 0; $i < sizeof($menu); $i++){ //percorre todo o cardapio do dia selecionado
            $menu[$i]->permission = 0; //seta permission 0 (sem permissão)

	    $agendado = array_search($menu[$i]->meal_id, $scheduling->toArray());
	    $menu[$i]->agendado = !is_bool($agendado);

            $allowMealDay = Allowstudenmealday::where('student_id', $user->student_id)
                ->where('meal_id', $menu[$i]->meal_id)
                ->get();
            try{
                if($allowMealDay){ //verifica se tem alguma permissão cadastrada
                    switch ($dayWeek) { //verifica o dia da semana e monta um if para saber se existe permissão, se sim adiciona permission 1 (com permissão)
                        case 0:
                            $menu[$i]->permission = 0;
                            break;
                        case 1:
                            if ($allowMealDay[0]->monday === 1) {
                                $menu[$i]->permission = 1;
                            }
                            break;
                        case 2:
                            if ($allowMealDay[0]->tuesday === 1) {
                                $menu[$i]->permission = 1;
                            }
                            break;
                        case 3:
                            if ($allowMealDay[0]->wednesday === 1) {
                                $menu[$i]->permission = 1;
                            }
                            break;
                        case 4:
                            if ($allowMealDay[0]->thursday === 1) {
                                $menu[$i]->permission = 1;
                            }
                            break;
                        case 5:
                            if ($allowMealDay[0]->friday === 1) {
                                $menu[$i]->permission = 1;
                            }
                            break;
                        case 6:
                            if ($allowMealDay[0]->saturday === 1) {
                                $menu[$i]->permission = 1;
                            }
                            break;
                    }
                }
            } catch (\ErrorException $e){
                continue;
            }

        }

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
                    'message' => 'A data não foi informada.'
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
                'message' => 'O Estudante não foi encontrado.'
            ], 404);
        }

        if (!$user->student_id){
            return response()->json([
                'message' => 'O Estudante não foi encontrado.'
            ], 202);
        }

        $student = Student::where('id', $user->student_id) ->with('course')->first();

        if ($user->campus_id!=$student->campus_id){
            return response()->json([
                'message' => 'O Estudante não pertence a este campus!'
            ], 404);
        }
        //verifica se o estudante possui refeições ausentes sem justificativa
        $dateNow = new \DateTime();
        $schedulingStudent = Scheduling::where('wasPresent', 0)
            ->where('absenceJustification', null)
            ->where('student_id', $student->id)
            ->where('campus_id', $user->campus_id)
            ->where('canceled_by_student', 0)
            ->where('date', '<',  $dateNow)
            ->get();
        if(sizeof($schedulingStudent)>0){
            $student->absent_meal = 1;
        } else {
            $student->absent_meal = 0;
        }

        return response()->json($student, 200);

    }

    public function showUser($id)
    {
        $user = User::where('id', $id)->first();
        if (!$user){
            return response()->json([
                'message' => 'O Estudante não foi encontrado.'
            ], 404);
        }

        if (!$user->student_id){
            return response()->json([
                'message' => 'O Estudante não foi encontrado.'
            ], 202);
        }

        return response()->json($user, 200);
    }

    public function studentByMatOrCod(Request $request)
    {
        $user = auth()->user();

        if(!$request->mat){
            return response()->json([
                'message' => 'A matrícula ou o código do estuante não foi informado.'
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

        if($student->dateValid < date('Y-m-d')){
            return response()->json([
                'message' => 'O estudante precisa fazer a atualização cadastral.'
            ], 202);
        }

        $schedulingStudent = Scheduling::where('wasPresent', 0)
            ->where('absenceJustification', null)
            ->where('student_id', $student->id)
            ->where('campus_id', $user->campus_id)
            ->where('date', '<', date('Y-m-d'))
            ->where('canceled_by_student', 0)
            ->get();

        if(sizeof($schedulingStudent)>5){
            return response()->json([
                'message' => 'O estudante está bloqueado.'
            ], 202);
        }

        return response()->json($student, 200);

    }

}
