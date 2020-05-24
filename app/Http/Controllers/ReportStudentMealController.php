<?php

namespace App\Http\Controllers;

use App\Allowstudenmealday;
use App\Campus;
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

        $qtdWasPresent = Scheduling::where('campus_id', $user->campus_id)
                ->where('wasPresent', 1)->count();

        $schedule = Scheduling::where('campus_id', $user->campus_id)
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

}
