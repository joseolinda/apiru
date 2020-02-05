<?php

namespace App\Http\Controllers;

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
        'login' => 'required',
        'password' => 'required',
        'type' => 'required',
        'campus_id' => 'required',
    ];
    private $messages = [
        'name.required' => 'O nome é obrigatório',
        'login.required' => 'O email é obrigatório',
        'login.unique' => 'USUÁRIO já está cadastrado.',
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

    public function index()
    {
        $users = User::get();
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
        $user = User::find($id);
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
        $validation = Validator::make($request->all(),$this->rules, $this->messages);

        if($validation->fails()){
            return $validation->errors()->toJson();
        }

        $user = User::find($id);

        if(!$user){
            return response()->json([
                'message' => 'Usuário não encontrado!'
            ], 404);
        }

        $user->name = $request->name;
        $user->login = $request->login;
        $user->password = Hash::make($request->password);
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
        $user->delete();

        return response()->json([
            'message' => 'Operação realizada com sucesso!'
        ], 200);
    }

    public function search($search)
    {
        $user = User::where( 'name', 'LIKE', '%' . $search . '%' )->get();
        if(!$user){
            return response()->json([
                'message' => 'Usuário não encontrado!'
            ], 404);
        }
        return response()->json($user, 200);
    }
}
