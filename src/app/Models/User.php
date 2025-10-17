<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract; // ← 追加
use Illuminate\Auth\MustVerifyEmail;                                      // ← 追加
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmailContract     // ← 追加
{
    use HasFactory, Notifiable, MustVerifyEmail;                          // ← 追加

    protected $casts = [
        'email_verified_at' => 'datetime',                                // 念のため
    ];
}
