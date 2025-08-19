<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Guardian extends Authenticatable implements MustVerifyEmail {

    use HasApiTokens,HasFactory,Notifiable;

    protected $fillable = ['name','email','password','student_id'];
    protected $hidden = ['password','remember_token'];

    protected $casts    = ['email_verified_at' => 'datetime',];
    
    public function student(){
        
        return $this->belongsTo(Student::class);
    }

}
    

