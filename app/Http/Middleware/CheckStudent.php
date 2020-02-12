<?php

namespace App\Http\Middleware;

use Closure;

class CheckStudent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Verifica seo usuário está logado.
        if ( !auth()->check() )
            //return redirect()->route('login');
            return response()->json(["Erro 401"], 401);

        // Recupera o type do usuário logado
        $type = auth()->user()->type;

        // Verifica se é ESTUDANTE, se sim manda uma msg de erro.
        if ( $type == 'STUDENT' )
            return response()->json(["Erro 401"], 401);

        return $next($request);
    }
}
