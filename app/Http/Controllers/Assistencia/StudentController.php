<?php

namespace App\Http\Controllers\Assistencia;

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
                $query->where('name', 'like', '%'.$name.'%')
                        ->orWhere('mat', 'like', '%'.$name.'%');
                return $query;
            })
            ->with('course')
            ->with('shift')
            ->with('user')
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

        if(!$request->email){
            return response()->json([
                'message' => 'Informe o e-mail.'
            ], 404);
        }

        $verifyEmail = User::where('email', $request->email)->first();
        if($verifyEmail){
            return response()->json([
                'message' => 'E-mail já cadastrado.'
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

        $verify = Student::where('mat', $request->mat)->first();
        if($verify){
            return response()->json([
                'message' => 'Matrícula já cadastrada!'
            ], 202);
        }

        $user = auth()->user();

        $student = new Student();
        $student->name = $request->name;
        $student->mat = $request->mat;
        $student->campus_id = $user->campus_id;
        $student->course_id = $request->course_id;
        $student->shift_id = $request->shift_id;
        $student->dateValid = $request->dateValid;
        $student->active = 1;
        $request->semRegular ?
            $student->semRegular = $request->semRegular : $student->semRegular = 1 ;
        $student->save();

        $user = new User();
        $user->name = $student->name;
        $user->email = $request->email;
        $user->password = "123";
        $user->active = 1;
        $user->type = "STUDENT";
        $user->campus_id = $student->campus_id;
        $user->student_id = $student->id;
        $user->save();


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
        $student = Student::where('id', $id)->with('user')->first();
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

        $verify = Student::where('mat', $request->mat)->first();
        if($verify){
            if($verify->id != $id){
                return response()->json([
                    'message' => 'Matrícula já cadastrada!'
                ], 202);
            }
        }

        $student->name = $request->name;
        $student->mat = $request->mat;
        $student->campus_id = $user->campus_id;
        $student->course_id = $request->course_id;
        $student->shift_id = $request->shift_id;
        $student->dateValid = $request->dateValid;
        $request->semRegular ?
            $student->semRegular = $request->semRegular : $student->semRegular = 1 ;
        $request->active ?
                $student->active = $request->active : $student->active = 0;
        $student->save();

        $user = User::where('student_id', $student->id)->first();
        if($user){
            $user->name = $student->name;
            $request->email ?
                    $user->email = $request->email : null;
            $user->student_id = $student->id;
            $user->active = $student->active;
            $user->type = "STUDENT";
            $user->save();
        }

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

        $userStudent = User::where('student_id', $student->id)->first();
        if($userStudent){
            $userStudent->delete();
        }

        $student->delete();

        return response()->json([
            'message' => 'Operação realizada com sucesso!'
        ], 200);
    }

    public function all()
    {
        $user = auth()->user();
        $students = Student::where('campus_id', $user->campus_id)->get();
        return response()->json($students,200);
    }

    public function historyStudent($idStudent){
        $student = Student::where('id', $idStudent)->first();

        if(!$student){
            return response()->json([
                'message' => 'Estudante não encontrado.'
            ], 202);
        }

        $user = auth()->user();
        if($student->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'Estudante pertence a outro campus.'
            ], 202);
        }

        $hitory = Scheduling::where('student_id', $student->id)
            ->orderBy('id', 'desc')
            ->with('meal')
            ->with('student')
            ->paginate(10);

        return response()->json($hitory, 200);
    }

}
