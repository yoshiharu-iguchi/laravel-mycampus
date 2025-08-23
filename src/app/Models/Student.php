<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens,HasFactory,Notifiable;

    protected $fillable = ['name','student_number','password','email','address','guardian_registration_token',];

    protected $hidden = ['password','remember_token','guardian_registration_token',];

    protected $casts = ['email_verified_at' => 'datetime',];

    public static function issueGuardianToken(int $days = 30): array
    {
        $uuid = (string) Str::uuid();
        $raw  = $uuid . now()->timestamp;
        $token = hash('sha256', $raw);

        return [$token, now()->addDays($days)];
    }

    public function guardian() 
    {
        return $this->hasOne(Guardian::class);
    }  
}
    
