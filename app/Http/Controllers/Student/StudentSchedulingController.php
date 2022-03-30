<?php

namespace App\Http\Controllers\Student;

use App\Allowstudenmealday;
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
use Illuminate\Support\Facades\Date;
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
                'message' => 'O estudante não foi encontrado.'
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
                'message' => 'A refeição não foi informada.'
            ], 202);
        }

        if(!$request->date){
            return response()->json([
                'message' => 'A data não foi informada.'
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
                'message' => 'O Estudante não foi encontrado.'
            ], 202);
        }

       if($student->active == 0){
            return response()->json([
                'message' => 'O Estudante encontra-se inativo.'
            ], 202);
        }

        $dateNow = new \DateTime();
        $dateValid = new \DateTime($student->dateValid);
        //dd($dateNow >= $dateValid);
        if($dateNow >= $dateValid){
            return response()->json([
                'message' => 'O Estudante encontra-se com o cadastro desatualizado.'
            ], 202);
        }

        //verifica se o estudante possui refeições ausentes sem justificativa
        $schedulingStudent = Scheduling::where('wasPresent', 0)
            ->where('absenceJustification', null)
            ->where('student_id', $student->id)
            ->where('campus_id', $user->campus_id)
            ->where('canceled_by_student', 0)
            ->where('date', '<',  $dateNow->format('Y-m-d'))
            ->get();
        if(sizeof($schedulingStudent)>=1){
            return response()->json([
                'message' => 'O estudante esteve ausente em alguma refeição. É necessário justificá-la.'
            ], 202);
        }



        $meal = Meal::where('id', $request->meal_id)->first();
        if(!$meal){
            return response()->json([
                'message' => 'A Refeição não foi encontrada.'
            ], 202);
        }

        //verifica se o estudante possui permissão e acrescenta um atributo permission ao response
        //pega o dia da semana 0 - Domingo ... 6 - Sábado
        $dayWeek = date('w', strtotime($request->date));
        $permission = 0;
        $allowMealDay = Allowstudenmealday::where('student_id', $user->student_id)
            ->where('meal_id', $meal->id)
            ->get();
        try{
            if($allowMealDay){ //verifica se tem alguma permissão cadastrada
                switch ($dayWeek) { //verifica o dia da semana e monta um if para saber se existe permissão, se sim adiciona permission 1 (com permissão)
                    case 0:
                        $permission = 0;
                        break;
                    case 1:
                        if ($allowMealDay[0]->monday === 1) {
                            $permission = 1;
                        }
                        break;
                    case 2:
                        if ($allowMealDay[0]->tuesday === 1) {
                            $permission = 1;
                        }
                        break;
                    case 3:
                        if ($allowMealDay[0]->wednesday === 1) {
                            $permission = 1;
                        }
                        break;
                    case 4:
                        if ($allowMealDay[0]->thursday === 1) {
                            $permission = 1;
                        }
                        break;
                    case 5:
                        if ($allowMealDay[0]->friday === 1) {
                            $permission = 1;
                        }
                        break;
                    case 6:
                        if ($allowMealDay[0]->saturday === 1) {
                            $permission = 1;
                        }
                        break;
                }
            }
        } catch (\ErrorException $e){

        }
        if($permission == 0){
            return response()->json([
                'message' => 'O usuário não possui permissão para reservar esta refeição.'
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
            ->where('campus_id', $user->campus_id)
            ->first();

        if($schedulingVerify){
            return response()->json([
                'message' => 'Não foi possível solicitar a reserva.
                Procure a Assistência Estudantil do seu campus.'
            ], 202);
        }

        $dataStart = new \DateTime( $request->date .' '. $meal->timeStart);
        $dataStart->sub(new \DateInterval('PT'.$meal->qtdTimeReservationStart.'H'));

        $dataEnd = new \DateTime( $request->date .' '. $meal->timeStart);
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

    public function cancelScheduling(Request $request)
    {

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
        if($user->student_id == null){
            return response()->json([
                'message' => 'O usuário não é um estudante.'
            ], 202);
        }

        $student = Student::where('id', $user->student_id)->first();
        if(!$student){
            return response()->json([
                'message' => 'O Estudante não foi encontrado.'
            ], 202);
        }

        $meal = Meal::where('id', $request->meal_id)->first();
        if(!$meal){
            return response()->json([
                'message' => 'A Refeição não foi encontrada.'
            ], 202);
        }

        $scheduling = Scheduling::where('date', $request->date)
            ->where('meal_id', $request->meal_id)
            ->where('student_id', $student->id)
            ->where('campus_id', $user->campus_id)
            ->first();

        if(!$scheduling){
            return response()->json([
                'message' => 'O agendamento não foi encontrado.'
            ], 202);
        }

        if($scheduling->canceled_by_student == 1){
            return response()->json([
                'message' => 'Reserva já cancelada pelo estudante.'
            ], 202);
        }

        $dataEnd = new \DateTime( $request->date .' '. $meal->timeEnd);
        $dataEnd->sub(new \DateInterval('PT'.$meal->qtdTimeReservationEnd.'H'));

        $dateNow = new \DateTime();

        if($dateNow > $dataEnd){
            return response()->json([
                'message' => 'O cancelamento está fora do horário permitido.'
            ], 202);
        }

        $scheduling->canceled_by_student = 1;
        $scheduling->save();

        return response()->json($scheduling, 200);

    }

    public function schedulings_canceled(Request $request){
        $user = auth()->user();

        $schedulings = Scheduling::where('student_id', $user->student_id)
            ->where('canceled_by_student', 1)
            ->orderBy('id', 'DESC')
            ->with('menu')
            ->with('meal')
            ->paginate(10);

        return response()->json($schedulings, 200);
    }

    public function schedulings_to_use(Request $request){
        $user = auth()->user();

        $dateNow = date('Y-m-d');

        $schedulings = Scheduling::where('student_id', $user->student_id)
            ->where('canceled_by_student', 0)
            ->where('date', '>=', $dateNow)
            ->where('wasPresent', 0)
            ->whereNull('time')
            ->orderBy('id', 'DESC')
            ->with('menu')
            ->with('meal')
            ->paginate(10);

        return response()->json($schedulings, 200);
    }

    public function schedulings_used(Request $request){
        $user = auth()->user();

        $schedulings = Scheduling::where('student_id', $user->student_id)
            ->where('canceled_by_student', 0)
            ->where('wasPresent', 1)
            ->whereNotNull('time')
            ->orderBy('id', 'DESC')
            ->with('menu')
            ->with('meal')
            ->paginate(10);

        return response()->json($schedulings, 200);
    }

    public function schedulings_not_used(Request $request){
        $user = auth()->user();

        $dateNow = new \DateTime();

        $schedulings = Scheduling::where('student_id', $user->student_id)
            ->where('canceled_by_student', 0)
            ->where('date', '<=', $dateNow)
            ->where('wasPresent', 0)
            ->orderBy('id', 'DESC')
            ->with('menu')
            ->with('meal')
            ->paginate(10);

        return response()->json($schedulings, 200);
    }

    public function allowsMealByDay(Request $request)
    {
        $user = auth()->user();
        $student = Student::where('id', $user->student_id)->first();
        if(!$student){
            return response()->json([
                'message' => 'O Estudante não foi encontrado.'
            ], 404);
        }
        if($user->campus_id != $student->campus_id){
            return response()->json([
                'message' => 'As Permissões fazem pertencem a outro campus!'
            ], 202);
        }
        $allowstudenmealday = Allowstudenmealday::where('student_id', $student->id)
            ->with('meal')
            ->with('student')
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($allowstudenmealday, 200);
    }

}
