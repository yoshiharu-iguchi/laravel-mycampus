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

Route::middleware('guest:admin')->group(function(){
    Route::get('admin/login',[Admin\Auth\AuthenticatedSessionController::class,'create'])
                ->name('admin.login');
    Route::post('admin/login',[Admin\Auth\AuthenticatedSessionController::class,'store']);
});

Route::middleware('auth')->group(function () {
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

Route::middleware('guest:student')->group(function () {
    Route::get('student/register', [\App\Http\Controllers\Student\Auth\RegisteredUserController::class, 'create'])
        ->name('student.register');
    Route::post('student/register', [\App\Http\Controllers\Student\Auth\RegisteredUserController::class, 'store']);

    Route::get('student/login', [\App\Http\Controllers\Student\Auth\AuthenticatedSessionController::class, 'create'])
        ->name('student.login');
    Route::post('student/login', [\App\Http\Controllers\Student\Auth\AuthenticatedSessionController::class, 'store']);

    Route::get('student/forgot-password', [\App\Http\Controllers\Student\Auth\PasswordResetLinkController::class, 'create'])
        ->name('student.password.request');
    Route::post('student/forgot-password', [\App\Http\Controllers\Student\Auth\PasswordResetLinkController::class, 'store'])
        ->name('student.password.email');

    Route::get('student/reset-password/{token}', [\App\Http\Controllers\Student\Auth\NewPasswordController::class, 'create'])
        ->name('student.password.reset');
    Route::post('student/reset-password', [\App\Http\Controllers\Student\Auth\NewPasswordController::class, 'store'])
        ->name('student.password.store');
});

Route::middleware('auth:student')->group(function () {
    Route::get('student/verify-email', [\App\Http\Controllers\Student\Auth\EmailVerificationPromptController::class, '__invoke'])
        ->name('student.verification.notice');

    Route::get('student/verify-email/{id}/{hash}', [\App\Http\Controllers\Student\Auth\VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('student.verification.verify');

    Route::post('student/email/verification-notification', [\App\Http\Controllers\Student\Auth\EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('student.verification.send');

    Route::get('student/confirm-password', [\App\Http\Controllers\Student\Auth\ConfirmablePasswordController::class, 'show'])
        ->name('student.password.confirm');
    Route::post('student/confirm-password', [\App\Http\Controllers\Student\Auth\ConfirmablePasswordController::class, 'store']);

    Route::put('student/password', [\App\Http\Controllers\Student\Auth\PasswordController::class, 'update'])
        ->name('student.password.update');

    Route::post('student/logout', [\App\Http\Controllers\Student\Auth\AuthenticatedSessionController::class, 'destroy'])
        ->name('student.logout');
});


/* -------------------------------
 | 保護者（guardian）
 *------------------------------- */
Route::middleware('guest:guardian')->group(function () {
    Route::get('guardian/register', [\App\Http\Controllers\Guardian\Auth\RegisteredUserController::class, 'create'])
        ->name('guardian.register');
    Route::post('guardian/register', [\App\Http\Controllers\Guardian\Auth\RegisteredUserController::class, 'store']);

    Route::get('guardian/login', [\App\Http\Controllers\Guardian\Auth\AuthenticatedSessionController::class, 'create'])
        ->name('guardian.login');
    Route::post('guardian/login', [\App\Http\Controllers\Guardian\Auth\AuthenticatedSessionController::class, 'store']);

    Route::get('guardian/forgot-password', [\App\Http\Controllers\Guardian\Auth\PasswordResetLinkController::class, 'create'])
        ->name('guardian.password.request');
    Route::post('guardian/forgot-password', [\App\Http\Controllers\Guardian\Auth\PasswordResetLinkController::class, 'store'])
        ->name('guardian.password.email');

    Route::get('guardian/reset-password/{token}', [\App\Http\Controllers\Guardian\Auth\NewPasswordController::class, 'create'])
        ->name('guardian.password.reset');
    Route::post('guardian/reset-password', [\App\Http\Controllers\Guardian\Auth\NewPasswordController::class, 'store'])
        ->name('guardian.password.store');
});

Route::middleware('auth:guardian')->group(function () {
    Route::get('guardian/verify-email', [\App\Http\Controllers\Guardian\Auth\EmailVerificationPromptController::class, '__invoke'])
        ->name('guardian.verification.notice');

    Route::get('guardian/verify-email/{id}/{hash}', [\App\Http\Controllers\Guardian\Auth\VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('guardian.verification.verify');

    Route::post('guardian/email/verification-notification', [\App\Http\Controllers\Guardian\Auth\EmailVerificationNotificationController::class, 'store'])
        ->
        middleware('throttle:6,1')
        ->name('guardian.verification.send');

    Route::get('guardian/confirm-password', [\App\Http\Controllers\Guardian\Auth\ConfirmablePasswordController::class, 'show'])
        ->name('guardian.password.confirm');
    Route::post('guardian/confirm-password', [\App\Http\Controllers\Guardian\Auth\ConfirmablePasswordController::class, 'store']);

    Route::put('guardian/password', [\App\Http\Controllers\Guardian\Auth\PasswordController::class, 'update'])
        ->name('guardian.password.update');

    Route::post('guardian/logout', [\App\Http\Controllers\Guardian\Auth\AuthenticatedSessionController::class, 'destroy'])
        ->name('guardian.logout');
});


/* -------------------------------
 | 教員（teacher）
 *------------------------------- */
Route::middleware('guest:teacher')->group(function () {
    Route::get('teacher/register', [\App\Http\Controllers\Teacher\Auth\RegisteredUserController::class, 'create'])
        ->name('teacher.register');
    Route::post('teacher/register', [\App\Http\Controllers\Teacher\Auth\RegisteredUserController::class, 'store']);

    Route::get('teacher/login', [\App\Http\Controllers\Teacher\Auth\AuthenticatedSessionController::class, 'create'])
        ->name('teacher.login');
    Route::post('teacher/login', [\App\Http\Controllers\Teacher\Auth\AuthenticatedSessionController::class, 'store']);

    Route::get('teacher/forgot-password', [\App\Http\Controllers\Teacher\Auth\PasswordResetLinkController::class, 'create'])
        ->name('teacher.password.request');
    Route::post('teacher/forgot-password', [\App\Http\Controllers\Teacher\Auth\PasswordResetLinkController::class, 'store'])
        ->name('teacher.password.email');

    Route::get('teacher/reset-password/{token}', [\App\Http\Controllers\Teacher\Auth\NewPasswordController::class, 'create'])
        ->name('teacher.password.reset');
    Route::post('teacher/reset-password', [\App\Http\Controllers\Teacher\Auth\NewPasswordController::class, 'store'])
        ->name('teacher.password.store');
});

Route::middleware('auth:teacher')->group(function () {
    Route::get('teacher/verify-email', [\App\Http\Controllers\Teacher\Auth\EmailVerificationPromptController::class, '__invoke'])
        ->name('teacher.verification.notice');

    Route::get('teacher/verify-email/{id}/{hash}', [\App\Http\Controllers\Teacher\Auth\VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('teacher.verification.verify');

    Route::post('teacher/email/verification-notification', [\App\Http\Controllers\Teacher\Auth\EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('teacher.verification.send');

    Route::get('teacher/confirm-password', [\App\Http\Controllers\Teacher\Auth\ConfirmablePasswordController::class, 'show'])
        ->name('teacher.password.confirm');
    Route::post('teacher/confirm-password', [\App\Http\Controllers\Teacher\Auth\ConfirmablePasswordController::class, 'store']);

    Route::put('teacher/password', [\App\Http\Controllers\Teacher\Auth\PasswordController::class, 'update'])
        ->name('teacher.password.update');

    Route::post('teacher/logout', [\App\Http\Controllers\Teacher\Auth\AuthenticatedSessionController::class, 'destroy'])
        ->name('teacher.logout');
});