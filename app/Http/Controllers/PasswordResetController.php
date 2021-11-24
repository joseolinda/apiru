<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Notifications\PasswordResetRequestNotifications;
use App\Notifications\PasswordResetSuccessNotifications;
use App\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Validator;
use App\User;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Create token password reset
     *
     * @param  [string] email
     * @return [string] message
     */
    public function redefinePassword(Request $request){
        $rules = [
            'email' => 'required|string|email',
        ];

        $messages = [
            'email.required' => 'O EMAIL DO USUÁRIO é obrigatório.',
            'email.email' => 'O EMAIL DO USUÁRIO não é válido.',
        ];


        $validation = Validator::make($request->all(),$rules, $messages);

        if($validation->fails()){
            return $validation->errors()->toJson();
        }

        $user = User::where('email', $request->email)->first();
        if (!$user)
            return response()->json([
                'message' => 'O E-mail não foi encontrado.'], 404);
        /*         
        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => Str::random(60)
            ]
        );
        if ($user && $passwordReset)
            $user->notify(
                new PasswordResetRequestNotifications($passwordReset->token, $user)
            );

        */ 
        // Mudar a senha para uma string padrão, sem enviar e-mail
        $defult_pass = bcrypt(env("PASS_DEFAULT_RESET", "123")); 
        $user->password = $defult_pass;
        $update = $user->save();

        if ($update) {
            return response()->json([
                // 'message' => 'Enviamos o link de redefinição de senha para seu e-mail.'
                'message' => 'Senha redefinida para a senha padrão.'
            ]);
        }

        return response()->json([
            'message' => 'Não foi possível gerar uma nova senha.'
        ]);
    }
    /**
     * Find token password reset
     *
     * @param  [string] $token
     * @return [string] message
     * @return [json] passwordReset object
     */
    public function find($token)
    {
        $passwordReset = PasswordReset::where('token', $token)
            ->first();
        if (!$passwordReset)
            return response()->json([
                'message' => 'Este token de redefinição de senha é inválido.'
            ], 404);
        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return response()->json([
                'message' => 'Este token de redefinição de senha é inválido.'], 404);
        }

        return response()->json($passwordReset);
    }
    /**
     * Reset password
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @param  [string] token
     * @return [string] message
     * @return [json] user object
     */
    public function reset(Request $request)
    {

        $rules = [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'token' => 'required|string',
        ];

        $messages = [
            'email.required' => 'O EMAIL DO USUÁRIO é obrigatório.',
            'email.email' => 'O EMAIL DO USUÁRIO não é válido.',

            'password.required' => 'O PASSWORD DO USUÁRIO é obrigatório.',
            'token.required' => 'O TOKEN DO USUÁRIO é obrigatório.',
        ];


        $validation = Validator::make($request->all(),$rules, $messages);

        if($validation->fails()){
            return $validation->errors()->toJson();
        }

        $passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email]
        ])->first();

        if (!$passwordReset)
            return response()->json([
                'message' => 'Este token de redefinição de senha é inválido.'
            ], 404);
        $user = User::where('email', $passwordReset->email)->first();
        if (!$user)
            return response()->json([
                'message' => 'Não foi encontrado um usuário com esse endereço de e-mail.'
            ], 404);
        $user->password = $request->password;
        $user->save();
        $passwordReset->delete();
        $user->notify(new PasswordResetSuccessNotifications($user));
        return response()->json($user);
    }
}
