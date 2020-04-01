<?php

namespace App\Models;

use App\Models\Design;
use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use Notifiable, SpatialTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tagline',
        'about',
        'username',
        'location',
        'formatted_address',
        'available_to_hire'
    ];

    protected $spatialFields = [
        'location'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function designs()
    {
        return $this->hasMany(Design::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    //Equipos a los que pertenece el usuario
    public function teams()
    {
        return $this->belongsToMany(Team::class)->withTimestamps();
    }
    public function ownedTeams()
    {
        return $this->teams()->where('owner_id', $this->id);
    }
    public function isOwnerOfTeam($team)
    {
        return (bool)$this->teams()
                        ->where('id', $team->id)
                        ->where('owner_id', $this->id)
                        ->count();
    }

    // Relaciones para validación
    public function invitations()
    {
        return $this->hasMany(Invitation::class, '')
    }



     /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}