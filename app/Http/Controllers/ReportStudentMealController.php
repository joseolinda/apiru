<?php

namespace App\Http\Controllers;

use App\Allowstudenmealday;
use App\Campus;
use App\Meal;
use App\Scheduling;
use App\Student;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Validator;
use JWTAuth;

class ReportStudentMealController extends Controller
{
    public function listScheduling(Request $request)
    {
        $user = auth()->user();

        $date = $request->date;
        $meal_id = $request->meal_id;
        $situation = $request->situation;

        $qtdWasPresent = Scheduling::where('campus_id', $user->campus_id)
                ->where('wasPresent', 1)
                ->when($date, function ($query) use ($date) {
                    return $query->where('date', '=', $date);
                })
                ->when($meal_id, function ($query) use ($meal_id) {
                    return $query->where('meal_id', '=', $meal_id);
                })
                ->when($situation, function ($query) use ($situation) {
                    if($situation == 'P'){
                        $query->where('wasPresent', '=', 1);
                    } else if($situation == 'J'){
                        $query->where('wasPresent', '=', 0)
                                ->where('absenceJustification', '=', null);
                    } else if($situation == 'A'){
                        $query->where('wasPresent', '=', 0)
                            ->where('absenceJustification', '!=', null);
                    }
                    return $query;
                })
                ->count();

        $schedule = Scheduling::where('campus_id', $user->campus_id)
            ->when($date, function ($query) use ($date) {
                return $query->where('date', '=', $date);
            })
            ->when($meal_id, function ($query) use ($meal_id) {
                return $query->where('meal_id', '=', $meal_id);
            })
            ->when($situation, function ($query) use ($situation) {
                if($situation == 'P'){
                    $query->where('wasPresent', '=', 1);
                } else if($situation == 'A'){
                    $query->where('wasPresent', '=', 0)
                        ->where('absenceJustification', '=', null);
                } else if($situation == 'J'){
                    $query->where('wasPresent', '=', 0)
                        ->where('absenceJustification', '!=', null);
                }
                return $query;
            })
            ->with('meal')
            ->with('student')
            ->with('menu')
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->paginate(20);

        $qtd = (object)[
            'qtdWasPresent' => $qtdWasPresent

        ];

        return response()->json([$schedule,
            $qtd], 200);
    }

    public function allMeal(Request $request)
    {
        $user = auth()->user();
        $description = $request->description;

        $meals = Meal::where('campus_id', $user->campus_id)
            ->orderBy('description')
            ->get();

        return response()->json($meals, 200);

    }

}
