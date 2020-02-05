<?php

namespace App\Http\Controllers;

use App\Campus;
use App\Course;
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
        'campus_id' => 'required',
        'course_id' => 'required',
        'shift_id' => 'required',
        'dateValid' => 'required',
    ];
    private $messages = [
        'name.required' => 'O nome é obrigatório',
        'mat.required' => 'A matricula é obrigatória',
        'campus_id.required' => 'O Campus é obrigatório',
        'course_id.required' => 'O Curso é obrigatório',
        'shift_id.required' => 'O Turno é obrigatório',
        'dateValid.required' => 'A data de validade é obrigatória',
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

    public function verifyCourseValid($id){
        if(empty($id)) {
            return false;
        }
        $campus = Course::find($id);
        if(!$campus){
            return false;
        }
        return true;
    }

    public function verifyShiftValid($id){
            if(empty($id)) {
                return false;
            }
            $campus = Shift::find($id);
            if(!$campus){
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
    public function index()
    {

        $students = Student::get();
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
        $student = new Student();

        $validation = Validator::make($request->all(),$this->rules,$this->messages);

        if($validation->fails()){
            return $validation->errors()->toJson();
        }

        //Verificando se existe campus cadastrados.
        if(!$this->verifyCampusValid($request->campus_id)){
            return response()->json([
                'message' => 'Campus invalido!'
            ], 404);
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

        if(!$this->verifyDateValid($request->dateValid)){
            return response()->json([
                'message' => 'Data Invalida!'
            ], 404);
        }

        $student->name = $request->name;
        $student->mat = $request->mat;
        $student->campus_id = $request->campus_id;
        $student->course_id = $request->course_id;
        $student->shift_id = $request->shift_id;
        $student->dateValid = $request->dateValid;
        $student->active = $request->active;
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
            return $validation->errors()->toJson();
        }

        $student = Student::find($id);

        if(!$student){
            return response()->json([
                'message' => 'Estudante não encontrado!'
            ], 404);
        }

        $student->name = $request->name;
        $student->mat = $request->mat;
        $student->campus_id = $request->campus_id;
        $student->course_id = $request->course_id;
        $student->shift_id = $request->shift_id;
        $student->dateValid = $request->dateValid;
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
        $student->delete();

        return response()->json([
            'message' => 'Operação realizada com sucesso!'
        ], 200);
    }

    public function search($search)
    {
        $student = Student::where( 'name', 'LIKE', '%' . $search . '%' )->orWhere( 'id', 'LIKE', '%' . $search . '%' )->get();
        if(!$student){
            return response()->json([
                'message' => 'Estudante não encontrado!'
            ], 404);
        }
        return response()->json($student, 200);
    }
}
