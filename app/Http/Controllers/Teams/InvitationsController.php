<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Mail\SendInvitationToJoinTeam;
use App\Models\Team;
use App\Repositories\Contracts\IInvitation;
use App\Repositories\Contracts\ITeam;
use App\Repositories\Contracts\IUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InvitationsController extends Controller
{
    //
    protected $invitations;
    protected $teams;
    protected $users;

    public function __construct(IInvitation $invitations, ITeam $teams, IUser $users)
    {
    	$this->invitations = $invitations;
    	$this->teams = $teams;
    	$this->users = $users;
    }

    public function invite(Request $request, $teamId)
    {
    	// get the team
    	$team = $this->teams->find($teamId);

    	$this->validate($request, [
    		'email'	=> ['required', 'email']
    	]);
    	$user = auth()->user();
    	// check if the user owns the team
    	if(! $user->isOwnerOfTeam($team)){
    		return response()->json(['email'=>'No eres el dueño del equipo'], 401);
    	}

    	// Verificar si el correo electrónico tiene una invitación pendiente
    	if($team->hasPendingInvite($request->email)){
    		return response()->json(['email'=>'El correo electrónico ya tiene una invitación pendiente'], 422);
    	}

    	// Obtener el destinatario (recipient) por correo electrónico
    	$recipient = $this->users->findByEmail($request->email);

    	// si el destinatario (recipient) no existe, envíe una invitación para unirse al team
    	if(! $recipient){
    		$this->createInvitation(false, $team, $request->email);
    		/*$invitation = $this->invitations->create([
    			'team_id' => $team->id,
    			'sender_id' => $user->id,
    			'recipient_email' => $request->email,
    			'token' => md5(uniqid(microtime()))
    		]);
    		Mail::to($request->email)->send(new SendInvitationToJoinTeam($invitation, false));*/

    		return response()->json([
    			'message' => 'Invitación enviar al usuario'
    		], 200);
    	}

    	// Verifica si el equipo ya tiene la usuario.
    	if($team->hasUser($recipient)){
    		return response()->json([
    			'message' => 'Este usuario ya parece ser un miembro del equipo'
    		], 422);
    	}

    	// Enviar la invitación al usuario.
    	$this->createInvitation(true, $team, $request->email);
    	return response()->json([
    			'message' => 'Invitación enviado al usuario'
    		], 200);
    }

    public function resend($id)
    {
    	$invitation = $this->invitations->find($id);
    	$this->authorize('resend', $invitation);
    	/*if(! auth()->user()->isOwnerOfTeam($invitation->team)){
    		return response()->json(['email'=>'No eres la dueña del equipo'], 401);
    	}*/
    	$recipient = $this->users->findByEmail($invitation->recipient_email);
    	Mail::to($invitation->recipient_email)->send(new SendInvitationToJoinTeam($invitation, !is_null($recipient)));
    	return response()->json([
    			'message' => 'Invitation resent'
    		], 200);
    }

    public function respond(Request $request, $id)
    {
    	$this->validate($request, [
    		'token' => ['required'],
    		'decision' => ['required']
    	]);
    	$token = $request->token;
    	$decision = $request->decision; // 'Acepto' o 'Denego'
    	$invitation = $this->invitations->find($id);

    	// Verifica si la invitación pertenece a esta usuario.
    	$this->authorize('respond', $invitation);
    	/*if($invitation->recipient_email !== auth()->user()->email){
    		return response()->json([
    			'message' => 'Esta no es tu invitación'
    		], 401);
    	}*/

    	// compruebe para asegurarse de que los tokens coinciden
    	if($invitation->token !== $token){
    		return response()->json([
    			'message' => 'Token Invalido'
    		], 401);
    	}

    	// Marque si se acepta
    	if($decision !== 'deny'){
    		$this->invitations->addUserToTeam($invitation->team, auth()->id());
    	}
    	$invitation->delete();
		return response()->json(['message' => 'Successful'], 200);

    }

    public function destroy($id)
    {
    	$invitation = $this->invitations->find($id);
    	$this->authorize('delete', $invitation);
    	$invitation->delete();

		return response()->json(['message' => 'Borrado'], 200);
    }

    public function createInvitation(bool $user_exists, Team $team, string $email)
    {
    	$invitation = $this->invitations->create([
    			'team_id' => $team->id,
    			'sender_id' => auth()->id(),
    			'recipient_email' => $email,
    			'token' => md5(uniqid(microtime()))
    		]);
    		Mail::to($email)
    			->send(new SendInvitationToJoinTeam($invitation, $user_exists));
    }
}






