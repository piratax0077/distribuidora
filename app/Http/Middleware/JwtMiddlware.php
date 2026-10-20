<?php

namespace App\Http\Middleware;

use App\cliente_modelo;
use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Illuminate\Http\Request;

class JwtMiddlware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json(['status' => 'Token is Invalid'], 401);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json(['status' => 'Token is Expired'], 401);
            }else{
                return response()->json(['status' => 'Authorization Token not found!'], 401);
            }
        }

        $rut = preg_replace('/[^0-9kK]/', '', (string) ($user->rut ?? ''));
        $cliente = cliente_modelo::where(function ($query) use ($rut, $user) {
            if ($rut !== '') {
                $query->whereRaw(
                    "UPPER(REPLACE(REPLACE(rut, '.', ''), '-', '')) = ?",
                    [strtoupper($rut)]
                );
            }

            if (!empty($user->email)) {
                $method = $rut !== '' ? 'orWhereRaw' : 'whereRaw';
                $query->{$method}('LOWER(email) = ?', [strtolower(trim($user->email))]);
            }
        })->first();

        if ($cliente && intval($cliente->activo) === 2) {
            try {
                JWTAuth::invalidate(JWTAuth::getToken());
            } catch (Exception $e) {
                // El acceso se rechaza aunque el token ya no pueda invalidarse.
            }

            return response()->json([
                'message' => 'No puede ingresar porque su cuenta se encuentra morosa.' .
                    ($cliente->descripcion_activo ? ' Motivo: ' . $cliente->descripcion_activo : ''),
                'code' => 'CLIENTE_MOROSO',
            ], 403);
        }

        return $next($request);
    }
}
