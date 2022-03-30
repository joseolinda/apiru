<?php

namespace App\Http\Controllers;

use App\Allowstudenmealday;
use App\Campus;
use App\Meal;
use App\Scheduling;
use App\Student;
use App\User;
use App\Course;
use Illuminate\Support\Facades\DB;
use Facade\Ignition\QueryRecorder\Query;
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
        $course = $request->course_id;

        if($course){
            $qtdWasPresent = DB::table('scheduling')
                ->select()
                ->leftJoin('meal', 'meal.id', '=', 'scheduling.meal_id')
                ->leftJoin('menu', 'menu.id', '=', 'scheduling.menu_id')
                ->leftJoin('student', 'student.id', '=', 'scheduling.student_id')
                ->leftJoin('course', 'course.id', '=', 'student.course_id')
                ->where('scheduling.wasPresent', '=', 1)
                ->where('scheduling.campus_id', '=', $user->campus_id)
                ->where('scheduling.date', '=', $date)
                ->where('scheduling.meal_id', '=', $meal_id)
                ->where('course.id','=',$course)
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
            

            $schedule = DB::table('scheduling')
                ->leftJoin('meal', 'meal.id', '=', 'scheduling.meal_id')
                ->leftJoin('menu', 'menu.id', '=', 'scheduling.menu_id')
                ->leftJoin('student', 'student.id', '=', 'scheduling.student_id')
                ->leftJoin('course', 'course.id', '=', 'student.course_id')
                ->select('course.*', 'meal.description as meal_description', 'student.*', 'scheduling.*')
                ->where('scheduling.campus_id', '=', $user->campus_id)
                ->where('scheduling.date', '=', $date)
                ->where('scheduling.meal_id', '=', $meal_id)
                ->where('course.id','=',$course)
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
                ->orderBy('scheduling.date', 'desc')
                ->orderBy('scheduling.time', 'desc')
                ->paginate(20);
            //dd($schedule->count(),$schedule->get());

            $qtd = (object)[
                'qtdWasPresent' => $qtdWasPresent
            ];

            return response()->json([$schedule,
                $qtd], 200);
        }
        else{

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

            // $schedule = Scheduling::where('campus_id', $user->campus_id)
            //     ->when($date, function ($query) use ($date) {
            //         return $query->where('date', '=', $date);
            //     })
            //     ->when($meal_id, function ($query) use ($meal_id) {
            //         return $query->where('meal_id', '=', $meal_id);
            //     })
            //     ->when($situation, function ($query) use ($situation) {
            //         if($situation == 'P'){
            //             $query->where('wasPresent', '=', 1);
            //         } else if($situation == 'A'){
            //             $query->where('wasPresent', '=', 0)
            //                 ->where('absenceJustification', '=', null);
            //         } else if($situation == 'J'){
            //             $query->where('wasPresent', '=', 0)
            //                 ->where('absenceJustification', '!=', null);
            //         }
            //         return $query;
            //     })
            //     ->with('meal')
            //     ->with('student')
            //     ->with('menu')
            //     ->orderBy('date', 'desc')
            //     ->orderBy('time', 'desc')
            //     ->paginate(20);
            
            $schedule = DB::table('scheduling')
                ->leftJoin('meal', 'meal.id', '=', 'scheduling.meal_id')
                ->leftJoin('menu', 'menu.id', '=', 'scheduling.menu_id')
                ->leftJoin('student', 'student.id', '=', 'scheduling.student_id')
                ->leftJoin('course', 'course.id', '=', 'student.course_id')
                ->select('course.*', 'meal.description as meal_description', 'student.*', 'scheduling.*')
                ->where('scheduling.campus_id', '=', $user->campus_id)
                ->when($date, function ($query) use ($date) {
                    return $query->where('scheduling.date', '=', $date);
                })
                ->when($meal_id, function ($query) use ($meal_id) {
                    return $query->where('scheduling.meal_id', '=', $meal_id);
                })
                ->when($situation, function ($query) use ($situation) {
                    if($situation == 'P'){
                        $query->where('scheduling.wasPresent', '=', 1);
                    } else if($situation == 'A'){
                        $query->where('scheduling.wasPresent', '=', 0)
                            ->where('scheduling.absenceJustification', '=', null);
                    } else if($situation == 'J'){
                        $query->where('scheduling.wasPresent', '=', 0)
                            ->where('scheduling.absenceJustification', '!=', null);
                    }
                    return $query;
                })
                ->orderBy('scheduling.date', 'desc')
                ->orderBy('scheduling.time', 'desc')
                ->paginate(20);

            $qtd = (object)[
                'qtdWasPresent' => $qtdWasPresent

            ];

            return response()->json([$schedule,
                $qtd], 200);
        }
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

    public function allCourse(Request $request)
    {
        $user = auth()->user();
        $courses = Course::where('campus_id', $user->campus_id)->get();
        return response()->json($courses,200);
    }

    public function listSchedulingPrint(Request $request)
    {
        $user = auth()->user();

        $date = $request->date;
        $meal_id = $request->meal_id;
        $situation = $request->situation;
        $course = $request->course_id;

        if($course){
            $qtdWasPresent = DB::table('scheduling')
                ->select()
                ->leftJoin('meal', 'meal.id', '=', 'scheduling.meal_id')
                ->leftJoin('menu', 'menu.id', '=', 'scheduling.menu_id')
                ->leftJoin('student', 'student.id', '=', 'scheduling.student_id')
                ->leftJoin('course', 'course.id', '=', 'student.course_id')
                ->where('scheduling.wasPresent', '=', 1)
                ->where('scheduling.campus_id', '=', $user->campus_id)
                ->where('scheduling.date', '=', $date)
                ->where('scheduling.meal_id', '=', $meal_id)
                ->where('course.id','=',$course)
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
            

            $schedule = DB::table('scheduling')
                ->leftJoin('meal', 'meal.id', '=', 'scheduling.meal_id')
                ->leftJoin('menu', 'menu.id', '=', 'scheduling.menu_id')
                ->leftJoin('student', 'student.id', '=', 'scheduling.student_id')
                ->leftJoin('course', 'course.id', '=', 'student.course_id')
                ->select('course.*', 'meal.description as meal_description', 'student.*', 'scheduling.*')
                ->where('scheduling.campus_id', '=', $user->campus_id)
                ->where('scheduling.date', '=', $date)
                ->where('scheduling.meal_id', '=', $meal_id)
                ->where('course.id','=',$course)
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
                ->orderBy('scheduling.date', 'desc')
                ->orderBy('scheduling.time', 'desc')
                ->get();
            //dd($schedule->count(),$schedule->get());

            $qtd = (object)[
                'qtdWasPresent' => $qtdWasPresent
            ];

            return response()->json([$schedule,
                $qtd], 200);
        }
        else{

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

            
            $schedule = DB::table('scheduling')
                ->leftJoin('meal', 'meal.id', '=', 'scheduling.meal_id')
                ->leftJoin('menu', 'menu.id', '=', 'scheduling.menu_id')
                ->leftJoin('student', 'student.id', '=', 'scheduling.student_id')
                ->leftJoin('course', 'course.id', '=', 'student.course_id')
                ->select('course.*', 'meal.description as meal_description', 'student.*', 'scheduling.*')
                ->where('scheduling.campus_id', '=', $user->campus_id)
                ->when($date, function ($query) use ($date) {
                    return $query->where('.scheduling.date', '=', $date);
                })
                ->when($meal_id, function ($query) use ($meal_id) {
                    return $query->where('scheduling.meal_id', '=', $meal_id);
                })
                ->when($situation, function ($query) use ($situation) {
                    if($situation == 'P'){
                        $query->where('scheduling.wasPresent', '=', 1);
                    } else if($situation == 'A'){
                        $query->where('scheduling.wasPresent', '=', 0)
                            ->where('scheduling.absenceJustification', '=', null);
                    } else if($situation == 'J'){
                        $query->where('scheduling.wasPresent', '=', 0)
                            ->where('scheduling.absenceJustification', '!=', null);
                    }
                    return $query;
                })
                ->orderBy('scheduling.date', 'desc')
                ->orderBy('scheduling.time', 'desc')
                ->get();

            $qtd = (object)[
                'qtdWasPresent' => $qtdWasPresent

            ];

            return response()->json([$schedule,
                $qtd], 200);
        }
    }
}
