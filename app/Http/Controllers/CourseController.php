<?php

namespace App\Http\Controllers;

use App\Campus;
use App\Course;
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
        'campus_id' => 'required',
    ];
    private $messages =  [
        'description.required' => 'O Campus é obrigatório',
        'initials.required' => 'A sigla é obrigatório',
        'campus_id.required' => 'O Campus é obrigatório',
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
    public function index()
    {
        $courses = Course::get();
        return response()->json($courses);
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
            return $validation->errors()->toJson();
        }

        //Verificando se existe campus cadastrado.
        if(!$this->verifyCampusValid($request->campus_id)){
            return response()->json([
                'message' => 'Campus inválido!'
            ], 404);
        }

        $course->description = $request->description;
        $course->initials = $request->initials;
        $course->campus_id = $request->campus_id;
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
        $courses = Course::find($id);
        if(!$courses){
            return response()->json([
                'message' => 'Curso não encontrado!'
            ], 404);
        }
        return response()->json($courses);
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

        $course = Course::find($id);

        if(!$course){
            return response()->json([
                'message' => 'Curso não encontrado!'
            ], 404);
        }

        $course->description = $request->description;
        $course->initials = $request->initials;
        $course->campus_id = $request->campus_id;
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
                'message' => 'Curso não encontrado!'
            ], 404);
        }
        $course->delete();

        return response()->json([
            'message' => 'Operação realizada com sucesso!'
        ], 200);
    }

    public function search($search)
    {
        $course = Course::where( 'description', 'LIKE', '%' . $search . '%' )->get();
        if(!$course){
            return response()->json([
                'message' => 'Curso não encontrado!'
            ], 404);
        }
        return response()->json($course, 200);
    }
}
