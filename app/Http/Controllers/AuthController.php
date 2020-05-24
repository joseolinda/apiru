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
        'email' => 'required|unique:user',
        'type' => 'required',
        'campus_id' => 'required',
    ];
    private $messages = [
        'name.required' => 'O nome é obrigatório',
        'email.required' => 'O email é obrigatório',
        'email.unique' => 'USUÁRIO já está cadastrado.',
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

        //if ( !auth()->check() )
          //  return response()->json(["Erro 401"], 401);

        $validation = Validator::make($request->all(),$this->rules, $this->messages);

        if($validation->fails()){
            $erros = array('errors' => array(
                $validation->messages()
            ));
            $json_str = json_encode($erros);
            return response($json_str, 202);
        }

        //Verificando se existe campus cadastrados.
        if(!$this->verifyCampusValid($request->campus_id)){
            $erros = array('errors' => array(
                'message' => 'Campus Inválido!'
            ));
            $json_str = json_encode($erros);
            return response($json_str, 202);
        }

        if(!$this->verifyEmailValid($request->email))
        {
            return response()->json(['message' => 'E-mail Inválido!'], 202);
        }

        $user = auth()->user();

        $user = User::create([
            'name'    => $request->name,
            'email'    => $request->email,
            'type'    => $request->type,
            'password' => 123,
            'campus_id' =>$request->campus_id,
            'active' => 1,
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
        if(!$request->email && !$request->password ){
            return response()->json([
                'message' => 'Informe o email e senha.'
            ], 200);
        }

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
            //Pegar o name do Users
            'name' => auth('api')->getUser()->name,
            //Pegar o campi ao qual o Users faz paprte
            'campus' => auth('api')->getUser()->campus_id,
            //Pegar o campo de ativo do Users
            'active' => auth('api')->getUser()->active,
            'expires_in'   => auth('api')->factory()->getTTL() * 60
        ], 200);
    }

    public function verifyEmailValid($email){
        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
            return true;
        } else {
            return false;
        }
    }
}
