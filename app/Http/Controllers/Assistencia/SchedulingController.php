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
}
