<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Mail;
use App\Mail\GuardianInviteMail;
use App\Models\Guardian;


class Student extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens,HasFactory,Notifiable;

    protected $fillable = ['name','student_number','password','email','address','guardian_registration_token',];

    protected $hidden = ['password','remember_token','guardian_registration_token',];

    protected $casts = ['email_verified_at' => 'datetime','guardian_registered_at' => 'datetime',];

    // 追加：モデルイベント 新規作成(insert)直前に未セットなら64桁トークン自動発行します。

    protected static function booted():void
    {
        static::creating(function (Student $student)
        {
            if (empty($student->guardian_registration_token)){
                [$token,$expiresAt] = self::issueGuardianToken(30);
                $student->guardian_registration_token = $token;
            }
        });
        static::created(function (Student $student) {
            Mail::to($student->email)->queue(new GuardianInviteMail($student));
        });
    }
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
    
    public function enrollments(){
        return $this->hasMany(Enrollment::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class,'enrollments')
        ->withPivot(['year','term','status','registered_at'])
        ->withTimestamps();
                    
    }
}
    
