<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;
    /*protected $redirectTo = RouteServiceProvider::HOME;
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }*/
     protected function attemptLogin(Request $request)
     {
        //Intentar emitir un token para el usuario en función de las credenciales de inicio de sesión
        $token = $this->guard()->attempt($this->credentials($request));

        if(! $token){
            return false;
        }

        //Obtener el usuario autenticado
        $user = $this->guard()->user();

        if($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail())
        {
            return false;
        }

        //establecer el token del usuario
        $this->guard()->setToken($token);

        return true;

     }
    protected function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);

        //Obtenga el token del protector de autenticación (JWT)
        $token = (string)$this->guard()->getToken();

        //Extraer la fecha de vencimiento del token
        $expiration = $this->guard()->getPayload()->get('exp');

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expiration
        ]);
    }
    protected function sendFailedLoginResponse(Request $request)
    {
        $user = $this->guard()->user();

        if($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail())
        {
            return response()->json(["errore"=>[
                "verification" => "You need to verify your email account"
            ]]);
        }

        throw ValidationException::withMessages([
            $this->username() => "Invalid credentials"
        ]);
    }

    public function logout()
    {
        $this->guard()->logout();
        return response()->json(['message' => 'Logged out seccessfully!']);
    }

}
