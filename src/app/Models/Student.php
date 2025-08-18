<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens,HasFactory,Notifiable;

    protected $fillable = ['name','student_number','email','password'];

    protected $hidden = ['password','remember_token'];

    public function guardian() 
    {
        return $this->hasOne(Guardian::class);
    }  
}
    
