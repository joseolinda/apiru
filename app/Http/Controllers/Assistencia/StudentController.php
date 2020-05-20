<?php

namespace App\Http\Controllers\Assistencia;

use App\Campus;
use App\Course;
use App\Http\Controllers\Controller;
use App\Scheduling;
use App\Shift;
use App\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\User as Authenticatable;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    private $rules = [
        'name' => 'required',
        'mat' => 'required',
        'course_id' => 'required',
        'shift_id' => 'required',
        'dateValid' => 'required',
        'semRegular' => 'required',
    ];
    private $messages = [
        'name.required' => 'O nome é obrigatório',
        'mat.required' => 'A matricula é obrigatória',
        'mat.unique' => 'A matricula deve ser única',
        'course_id.required' => 'O Curso é obrigatório',
        'shift_id.required' => 'O Turno é obrigatório',
        'dateValid.required' => 'A data de validade é obrigatória',
        'semRegular.required' => 'Informe se é semestre regular',
    ];

    public function verifyCourseValid($id){
        if(empty($id)) {
            return false;
        }
        $course = Course::find($id);
        if(!$course){
            return false;
        }
        $user = auth()->user();
        if($course->campus_id != $user->campus_id){
            return false;
        }
        return true;
    }

    public function verifyShiftValid($id){
            if(empty($id)) {
                return false;
            }
            $shift = Shift::find($id);
            if(!$shift){
                return false;
            }
            $user = auth()->user();
            if($shift->campus_id != $user->campus_id){
                return false;
            }
            return true;
    }

    public function verifyDateValid($dateValid){
        if(empty($dateValid)) {
            return false;
        }
        $dt_verify = date('Y-m-d', strtotime($dateValid));
        $dt_actual = date("Y-m-d");
        if(($dt_verify)<(strtotime($dt_actual))){
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

        $name = $request->name;
        $mat = $request->mat;
        $students = Student::when($name, function ($query) use ($name) {
                return $query->where('name', 'like', '%'.$name.'%');
            })
            ->when($mat, function ($query) use ($mat) {
                return $query->where('mat', 'like', '%'.$mat.'%');
            })->with('course')->with('shift')
            ->where('campus_id', $user->campus_id)
            ->orderBy('name')
            ->paginate(10);

        return response()->json($students);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(),$this->rules,$this->messages);

        if($validation->fails()){
            $erros = array('errors' => array(
                $validation->messages()
            ));
            $json_str = json_encode($erros);
            return response($json_str, 202);
        }

        if(!$this->verifyCourseValid($request->course_id)){
            return response()->json([
                'message' => 'Curso invalido!'
            ], 404);
        }

        if(!$this->verifyShiftValid($request->shift_id)){
            return response()->json([
                'message' => 'Turno invalido!'
            ], 404);
        }
        /*
        if(!$this->verifyDateValid($request->dateValid)){
            return response()->json([
                'message' => 'Data Invalida!'
            ], 404);
        }
*/
        $user = auth()->user();

        $student = new Student();
        $student->name = $request->name;
        $student->mat = $request->mat;
        $student->campus_id = $user->campus_id;
        $student->course_id = $request->course_id;
        $student->shift_id = $request->shift_id;
        $student->dateValid = $request->dateValid;
        $student->active = 1;
        $student->semRegular = $request->semRegular;
        $student->save();

        return response()->json(
            $student, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $student = Student::find($id);
        if (!$student){
            return response()->json([
                'message' => 'Estudante não encontrado!'
            ], 404);
        }

        $user = auth()->user();
        if($student->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'O Estudante pertence a outro campus.'
            ], 202);
        }
        return response()->json($student);
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

        $student = Student::find($id);

        if(!$student){
            return response()->json([
                'message' => 'Estudante não encontrado!'
            ], 404);
        }

        $user = auth()->user();
        if($student->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'O Estudante pertence a outro campus.'
            ], 202);
        }

        if(!$this->verifyCourseValid($request->course_id)){
            return response()->json([
                'message' => 'Curso invalido!'
            ], 404);
        }

        if(!$this->verifyShiftValid($request->shift_id)){
            return response()->json([
                'message' => 'Turno invalido!'
            ], 404);
        }

        $student->name = $request->name;
        $student->mat = $request->mat;
        $student->campus_id = $user->campus_id;
        $student->course_id = $request->course_id;
        $student->shift_id = $request->shift_id;
        $student->dateValid = $request->dateValid;
        $student->semRegular = $request->semRegular;
        $student->save();

        return response()->json($student, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $student = Student::find($id);
        if (!$student){
            return response()->json([
                'message' => 'Estudante não encontrado!'
            ], 404);
        }

        $user = auth()->user();
        if($student->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'O Estudante pertence a outro campus.'
            ], 202);
        }

        $scheduling = Scheduling::where('student_id', $student->id)->get();
        if(sizeof($scheduling)>0){
            return response()->json([
                'message' => 'O Estudante possui agendamentos.'
            ], 202);
        }

        $student->delete();

        return response()->json([
            'message' => 'Operação realizada com sucesso!'
        ], 200);
    }

}
