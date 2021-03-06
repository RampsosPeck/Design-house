<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Repositories\Contracts\IUser;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
//use Illuminate\Foundation\Auth\VerifiesEmails;

class VerificationController extends Controller
{

    protected $users;

    public function __construct(IUser $users)
    {
        //$this->middleware('auth');
        //$this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
        $this->users = $users;
    }

    public function verify(Request $request, User $user)
    {
        // Compruebe si la url es una url firmada válida
        if(! URL::hasValidSignature($request)){
            return response()->json(["errors"=> [
                "message" => "Invalid verification link or signature"
            ]], 422);
        }
        //Compruebe si el usuario ya ha verificado la cuenta
        if($user->hasVerifiedEmail()){
            return response()->json(["errors"=> [
                "message" => "Dirección de correo electrónico ya verificado."
            ]], 422);
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json([ 'message' => 'Email successfully verified'], 200);
    }

    public function resend(Request $request)
    {
        $this->validate($request, [
            'email' => ['email', 'required']
        ]);

        //$user = User::where('email',$request->email)->first();
        $user = $this->users->findWhereFirst('email', $request->email);

        if(! $user){
            return response()->json(["errors"=>[
                "email" => "No user could be found with this email address"
            ]], 422);
        }

        if($user->hasVerifiedEmail()){
            return response()->json(["errors"=> [
                "message" => "Dirección de correo electrónico ya verificado."
            ]], 422);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['status' => "verification link resent"]);
    }
}
