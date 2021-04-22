<?php

namespace App\Http\Controllers\Assistencia;

use App\Campus;
use App\Http\Controllers\Controller;
use App\Shift;
use App\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShiftController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    private $rules = [
        'description' => 'required',
    ];
    private $messages = [
        'description.required' => 'A descrição é obrigatória',
    ];

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
        $shifts = Shift::when($description, function ($query) use ($description) {
                        return $query->where('description', 'like', '%'.$description.'%');
                 })
            ->where('campus_id', $user->campus_id)
            ->orderBy('description')
            ->paginate(10);

        return response()->json($shifts, 200);
    }

    public function all(Request $request)
    {
        $user = auth()->user();

        $shifts = Shift::where('campus_id', $user->campus_id)->get();
        return response()->json($shifts,200);
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

        $user = auth()->user();

        $shift = new Shift();
        $shift->description = $request->description;
        $shift->campus_id = $user->campus_id;
        $shift->save();

        return response()->json($shift, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $shift = Shift::find($id);
        if(!$shift){
            return response()->json([
                'message' => 'O Turno não foi encontrado.'
            ], 404);
        }
        $user = auth()->user();
        if($shift->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'O Turno pertence a outro campus.'
            ], 202);
        }
        return response()->json($shift);
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

        $shift = Shift::find($id);

        $user = auth()->user();

        if(!$shift){
            return response()->json([
                'message' => 'O Turno não foi encontrado.'
            ], 404);
        }

        if($shift->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'O Turno pertence a outro campus.'
            ], 202);
        }

        $shift->description = $request->description;
        $shift->campus_id = $user->campus_id;
        $shift->save();

        return response()->json($shift, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $shift = Shift::find($id);
        if(!$shift){
            return response()->json([
                'message' => 'O Turno não foi encontrado.'
            ], 404);
        }
        $user = auth()->user();

        $shift = Shift::find($id);

        if($shift->campus_id != $user->campus_id){
            return response()->json([
                'message' => 'O Turno pertence a outro campus.'
            ], 202);
        }

        $student = Student::where('shift_id', $shift->id)->get();
        if(sizeof($student)>0){
            return response()->json([
                'message' => 'Existem alunos relacionados com este turno.'
            ], 202);
        }

        $shift->delete();

        return response()->json([
            'message' => 'O Turno foi excluído.'
        ], 200);
    }

}
