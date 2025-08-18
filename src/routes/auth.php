<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Student;
use App\Http\Controllers\Guardian;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest','guest:admin'])->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
                ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
                ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
                ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->name('password.store');
});

Route::middleware('guest:student')->group(function () {
    Route::get('student/register',  [Student\Auth\RegisteredUserController::class, 'create'])
        ->name('student.register');

    Route::post('student/register', [Student\Auth\RegisteredUserController::class, 'store'])
        ->name('student.register.store'); 
});

Route::middleware('guest:guardian')->group(function(){
    Route::get('guardian/register',[Guardian\Auth\RegisteredUserController::class,'create'])
        ->name('guardian.register');

    Route::post('guardian/register',[Guardian\Auth\RegisteredUserController::class,'store'])
        ->name('guardian.register.store');
});


Route::middleware('guest:admin')->group(function(){
    Route::get('admin/login',[Admin\Auth\AuthenticatedSessionController::class,'create'])
                ->name('admin.login');

    Route::post('admin/login',[Admin\Auth\AuthenticatedSessionController::class,'store'])
                ->name('admin.login.store');
});

Route::middleware('guest:student')->group(function () {
    Route::get('student/login',  [Student\Auth\AuthenticatedSessionController::class, 'create'])
        ->name('student.login');
    Route::post('student/login', [Student\Auth\AuthenticatedSessionController::class, 'store'])
        ->name('student.login.store');
});

Route::middleware('guest:guardian')->group(function(){
    Route::get('guardian/login',[Guardian\Auth\AuthenticatedSessionController::class,'create'])
        ->name('guardian.login');
    Route::post('guardian/register',[Guardian\Auth\AuthenticatedSessionController::class,'store'])
        ->name('guardian.register.store');
});


Route::middleware('auth:web,student,guardian')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
                ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
                ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');
});

Route::middleware('auth:admin')->group(function(){
    Route::post('admin/logout',[Admin\Auth\AuthenticatedSessionController::class,'destroy'])
                ->name('admin.logout');
});

Route::middleware('auth:student')->group(function () {
    Route::get('student/verify-email', [EmailVerificationPromptController::class, '__invoke'])
        ->name('student.verification.notice');

    // 認証リンク検証（署名付きURL）
    Route::get('student/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['signed','throttle:6,1'])
        ->name('student.verification.verify');

    // 認証メール再送
    Route::post('student/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware(['throttle:6,1'])
        ->name('student.verification.send');

    Route::post('student/logout', [Student\Auth\AuthenticatedSessionController::class, 'destroy'])
        ->name('student.logout');
});

