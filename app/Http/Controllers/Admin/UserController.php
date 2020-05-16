<?php

namespace App\Http\Controllers\Admin;

use App\Campus;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    private $rules = [
        'name' => 'required',
        'email' => 'required',
        'password' => 'required',
        'type' => 'required',
        'campus_id' => 'required',
    ];
    private $messages = [
        'name.required' => 'O nome é obrigatório',
        'email.required' => 'O email é obrigatório',
        'email.unique' => 'USUÁRIO já está cadastrado.',
        'password.required' => 'O password é obrigatório',
        'type.required' => 'O tipo é obrigatório',
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

    public function index(Request $request)
    {
        $name = $request->name;
        $users = User::when($name, function ($query) use ($name) {

            return $query->where('name', 'like', '%'.$name.'%');
        })
            ->with('campus')
            ->orderBy('name')
            ->paginate(10);

        return response()->json($users,200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::where('id',$id)
                ->with('campus')
                ->get();
        if(!$user){
            return response()->json([
                'message' => 'Usuário não encontrado!'
            ], 404);
        }
        return response()->json($user, 200);
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

        $validation = Validator::make($request->all(),
                [
                    'name' => 'required',
                    'email' => 'required',
                    'type' => 'required',
                    'campus_id' => 'required',
                    'active' => 'required',
                ],
                [
                    'name.required' => 'O NOME é obrigatório.',
                    'email.required' => 'O EMAIL é obrigatório.',
                    'type.required' => 'O TIPO é obrigatório.',
                    'campus_id.required' => 'O CAMPUS é obrigatório.',
                    'active.required' => 'A SITUAÇÃO é obrigatória.',
                ]
            );

        if($validation->fails()){
            $erros = array('errors' => array(
                $validation->messages()
            ));
            $json_str = json_encode($erros);
            return response($json_str, 202);
        }

        $user = User::find($id);

        if(!$user){
            return response()->json([
                'message' => 'Usuário não encontrado!'
            ], 404);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->type = $request->type;
        $user->campus_id = $request->campus_id;
        $user->active = $request->active;
        $user->save();

        return response()->json($user, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if(!$user){
            return response()->json([
                'message' => 'Usuário não encontrado!'
            ], 404);
        }
        //DEPOIS VERIFICAR SE TEMALGO QUE DEPENDA DO USUÁRIO
        $user->delete();

        return response()->json([
            'message' => 'Operação realizada com sucesso!'
        ], 200);
    }
}
