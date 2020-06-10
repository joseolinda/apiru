<?php

namespace App\Http\Controllers\Student;

use App\Campus;
use App\Course;
use App\Http\Controllers\Controller;
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

}
