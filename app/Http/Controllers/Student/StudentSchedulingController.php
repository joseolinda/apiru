<?php

namespace App\Http\Controllers\Student;

use App\Campus;
use App\Course;
use App\Http\Controllers\Controller;
use App\Meal;
use App\Menu;
use App\Scheduling;
use App\Shift;
use App\Student;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\User as Authenticatable;

class StudentSchedulingController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function schedulings(Request $request)
    {
        $user = auth()->user();

        $student = Student::where('id', $user->student_id)->first();

        if(!$student){
            return response()->json([
                'message' => 'O estudante não foi encontrado!'
            ], 202);
        }

        $scheduling = Scheduling::where('student_id', $student->id)
            ->with('meal')
            ->with('menu')
            ->orderBy('date', 'desc')
            ->paginate(10);

        return response()->json($scheduling, 200);
    }

    public function newScheduling(Request $request)
    {

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
        if($user->student_id == null){
            return response()->json([
                'message' => 'O usuário não é um estudante.'
            ], 202);
        }

        $student = Student::where('id', $user->student_id)->first();
        if(!$student){
            return response()->json([
                'message' => 'Estudante  não encontrado.'
            ], 202);
        }

        $meal = Meal::where('id', $request->meal_id)->first();
        if(!$meal){
            return response()->json([
                'message' => 'Refeição não encontrada.'
            ], 202);
        }

        $menu = Menu::where('meal_id', $request->meal_id)
            ->where('date', $request->date)
            ->where('campus_id', $user->campus_id)
            ->first();

        if(!$menu){
            return response()->json([
                'message' => 'Não existe cárdapio cadastrado para esta data.'
            ], 202);
        }

        $schedulingVerify = Scheduling::where('date', $request->date)
            ->where('meal_id', $request->meal_id)
            ->where('student_id', $student->id)
            ->first();

        if($schedulingVerify){
            return response()->json([
                'message' => 'O agendamento já foi registrado.'
            ], 202);
        }

        $dataStart = new \DateTime( $request->date .' '. $meal->timeStart);
        $dataStart->sub(new \DateInterval('PT'.$meal->qtdTimeReservationStart.'H'));

        $dataEnd = new \DateTime( $request->date .' '. $meal->timeEnd);
        $dataEnd->sub(new \DateInterval('PT'.$meal->qtdTimeReservationEnd.'H'));

        $dateNow = new \DateTime();

        if($dateNow < $dataStart || $dateNow > $dataEnd){
            return response()->json([
                'message' => 'O agendamento está fora do horário permitido.'
            ], 202);
        }

        $scheduling = new Scheduling();
        $scheduling->wasPresent = 0;
        $scheduling->canceled_by_student = 0;
        $scheduling->date = $request->date;
        $scheduling->campus_id = $user->campus_id;
        $scheduling->meal_id = $request->meal_id;
        $scheduling->menu_id = $menu->id;
        $scheduling->student_id = $student->id;
        $scheduling->dateInsert = \date('Y-m-d');

        $scheduling->save();

        return response()->json($scheduling, 200);

    }

}
