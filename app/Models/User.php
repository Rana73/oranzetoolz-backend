<?php

namespace App\Models;
use App\Models\UsersOtp;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Rest omitted for brevity

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
    static function generateResetPasswordCode()
    {
        $reset_code = rand(100000,999999);
        // $reset_code = str_replace("/", "_", Hash::make($reset_code));
        $reset_code = str_replace(str_split('\\/:*?"<>|+-.$'), '', Hash::make($reset_code));;
        if(User::where('pass_reset_code',$reset_code)->exists()){
            $reset_code = User::generateResetPasswordCode();
        }
        return $reset_code;
    }
    static function generateOTPCode()
    {
        $otp = rand(111111,999999);
        if(UsersOtp::where('otp',$otp)->exists()){
            $otp = UsersOtp::generateOTPCode();
        }
        return $otp;
    }
}