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
            ->where('campus_id', $user->campus_id)
            ->first();

        if($schedulingVerify){
            return response()->json([
                'message' => 'Não foi possível solicitar reserva.
                Procure a Assistência Estudantil.'
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

    public function cancelScheduling(Request $request)
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

        $dateNow = date('y-m-d');

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
            ->whereNull('time')
            ->orderBy('id', 'DESC')
            ->with('menu')
            ->with('meal')
            ->paginate(10);

        return response()->json($schedulings, 200);
    }

}
