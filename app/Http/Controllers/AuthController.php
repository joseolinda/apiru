<?php

namespace App\Http\Controllers;

use App\Campus;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Validator;
use JWTAuth;

class AuthController extends Controller
{
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



    public function register(Request $request){

        if ( !auth()->check() )
            return response()->json(["Erro 401"], 401);

        $validation = Validator::make($request->all(),$this->rules, $this->messages);

        if($validation->fails()){
            $erros = array('errors' => array(
                $validation->messages()
            ));
            //return response($validation->errors(), 200);
            //return $validation->messages()->toJson();
            $json_str = json_encode($erros);
            return response($json_str, 200);
        }

        //Verificando se existe campus cadastrados.
        if(!$this->verifyCampusValid($request->campus_id)){
            $erros = array('errors' => array(
                'message' => 'Campus Inválido!'
            ));
            $json_str = json_encode($erros);
            return response($json_str, 200);
        }

        $user = User::create([
            'name'    => $request->name,
            'email'    => $request->email,
            'type'    => $request->type,
            'password' => $request->password,
            'campus_id' =>$request->campus_id,
            'active' =>$request->active,
        ]);

        //dd($user);

        $token = auth()->login($user);

        return $this->respondWithToken($token);
    }

    public function login(Request $request){
        //$credentials =('email','password');
        $credentials = [
            "email"=>$request->email,
            "password"=>$request->password
        ];
        //dd($credentials);

        $token = auth('api')
            ->claims(['role' => '',
                'name' => ''])
            ->setTTL(1800)
            ->attempt($credentials);
        if (!$token) {
            return response()->json(['error' => 'Não Autorizado!'], 401);
        }

        $user = User::where([
            ['email', '=', request(['email'])],
        ])->first();
        auth('api')->setUser($user);

        return $this->respondWithToken($token);
    }

    public function logout(){
        auth('api')->logout();

        return response()->json(['message' => 'logout feito com sucesso!']);
    }

    protected function respondWithToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            //Pegar o type do users
            'classfication' => auth('api')->getUser()->type,
            'expires_in'   => auth('api')->factory()->getTTL() * 60
        ], 200);
    }
}
