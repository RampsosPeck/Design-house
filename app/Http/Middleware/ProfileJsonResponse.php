<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class ProfileJsonResponse
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

        $response = $next($request);

        // Verificar si debugbar está habilitado
        if(! app()->bound('debugbar') || ! app('debugbar')->isEnabled()){
            return $response;
        }

        // Perfil de la respuesta json
        if($response instanceof JsonResponse && $request->has('_debug')){
            //dd("debugging");
            /*$response->setData(array_merge($response->getData(true), [
                '__debugbar' => app('debugbar')->getData()
            ]));*/

            $response->setData(array_merge([
                '__debugbar' => Arr::only(app('debugbar')->getData(), 'queries')
            ], $response->getData(true)));
        }

        return $response;

    }

}


