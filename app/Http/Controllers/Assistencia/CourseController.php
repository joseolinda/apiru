<?php

namespace App\Http\Controllers\Assistencia;

use App\Campus;
use App\Course;
use App\Http\Controllers\Controller;
use App\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    private $rules = [
        'description' => 'required',
        'initials' => 'required',
    ];
    private $messages =  [
        'description.required' => 'A DESCRIÇÃO é obrigatória.',
        'initials.required' => 'A SIGLA é obrigatória.',
    ];

    //Métodos Criados
    public function verifyCampusValid($id){
        if(empty($id)) {
            return false;
        }
        $campus = Campus::find($id);
        if(!$campus){
            return false;
        }
        return true;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $description = $request->description;
        $courses = Course::when($description, function ($query) use ($description) {
                return $query->where('description', 'like', '%'.$description.'%')
                    ->orWhere('initials', 'like', '%'.$description.'%');
            })
            ->where('campus_id', $user->campus_id)
            ->orderBy('description')
            ->paginate(10);

        return response()->json($courses, 200);
    }

    public function all(Request $request)
    {
        $user = auth()->user();
        $courses = Course::where('campus_id', $user->campus_id)->get();
        return response()->json($courses,200);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $course = new Course();
        $validation = Validator::make($request->all(),$this->rules,$this->messages);

        if($validation->fails()){
            $erros = array('errors' => array(
                $validation->messages()
            ));
            $json_str = json_encode($erros);
            return response($json_str, 202);
        }

        $user = auth()->user();

        $course->description = $request->description;
        $course->initials = $request->initials;
        $course->campus_id = $user->campus_id;
        $course->save();

        return response()->json($course, 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $course = Course::find($id);
        if(!$course){
            return response()->json([
                'message' => 'O Curso não foi encontrado.'
            ], 404);
        }
        $user = auth()->user();
        if($course->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'O Curso pertence a outro campus.'
            ], 202);
        }

        return response()->json($course, 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make($request->all(),$this->rules,$this->messages);

        if($validation->fails()){
            $erros = array('errors' => array(
                $validation->messages()
            ));
            $json_str = json_encode($erros);
            return response($json_str, 202);
        }

        $course = Course::find($id);

        if(!$course){
            return response()->json([
                'message' => 'O Curso não foi encontrado.'
            ], 404);
        }

        $user = auth()->user();
        if($course->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'O Curso pertence a outro campus.'
            ], 202);
        }

        $course->description = $request->description;
        $course->initials = $request->initials;
        $course->campus_id = $user->campus_id;
        $course->save();

        return response()->json($course, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $course = Course::find($id);
        if (!$course){
            return response()->json([
                'message' => 'O Curso não foi encontrado.'
            ], 404);
        }
        $user = auth()->user();
        if($course->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'O Curso pertence a outro campus.'
            ], 202);
        }

        $student = Student::where('course_id', $course->id)->get();
        if(sizeof($student)>0){
            return response()->json([
                'message' => 'O Curso possui usuários cadastrados.'
            ], 202);
        }

        $course->delete();

        return response()->json([
            'message' => 'O Curso foi deletado.'
        ], 200);
    }
}
