<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin;
use App\Http\Controllers\Student;
use App\Http\Controllers\Guardian;
use App\Http\Controllers\Teacher;

// メール認証系（invoke → show/verify へ変更）
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use Illuminate\Auth\Notifications\VerifyEmail;

/* =========================
   Student: register / login / forgot / reset
   ========================= */
Route::middleware('guest:student')->group(function () {
    Route::get('student/register',  [Student\Auth\RegisteredUserController::class, 'create'])->name('student.register');
    Route::post('student/register', [Student\Auth\RegisteredUserController::class, 'store'])->name('student.register.store');

    Route::get('student/login',  [Student\Auth\AuthenticatedSessionController::class, 'create'])->name('student.login');
    Route::post('student/login', [Student\Auth\AuthenticatedSessionController::class, 'store'])->name('student.login.store');
});

Route::middleware('auth:student')->group(function () {
    // メール認証（未認証ユーザー向けの案内／再送）
    Route::get('student/verify-email', [EmailVerificationPromptController::class, '__invoke'])
        ->name('student.verification.notice');

    Route::get('student/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['signed','throttle:6,1'])
        ->name('student.verification.verify');

    Route::post('student/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('student.verification.send');

    // ログアウト
    Route::post('student/logout', [Student\Auth\AuthenticatedSessionController::class, 'destroy'])->name('student.logout');
});


/* =========================
   Guardian: register / login / forgot / reset
   ========================= */
Route::middleware('guest:guardian')->group(function () {
    Route::get('guardian/register',  [Guardian\Auth\RegisteredUserController::class, 'create'])->name('guardian.register');
    Route::post('guardian/register', [Guardian\Auth\RegisteredUserController::class, 'store'])->name('guardian.register.store');

    Route::get('guardian/login',  [Guardian\Auth\AuthenticatedSessionController::class, 'create'])->name('guardian.login');
    Route::post('guardian/login', [Guardian\Auth\AuthenticatedSessionController::class, 'store'])->name('guardian.login.store');
});

Route::middleware('auth:guardian')->group(function () {
    // メール認証（未認証ユーザー向けの案内／再送）
    Route::get('guardian/verify-email', [EmailVerificationPromptController::class, '__invoke'])
        ->name('guardian.verification.notice');

    Route::get('guardian/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['signed','throttle:6,1'])
        ->name('guardian.verification.verify');

    Route::post('guardian/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('guardian.verification.send');

    // ログアウト
    Route::post('guardian/logout', [Guardian\Auth\AuthenticatedSessionController::class, 'destroy'])->name('guardian.logout');
});


/* =========================
   Admin: login / logout（メール認証は不要）
   ========================= */
Route::middleware('guest:admin')->group(function () {
    Route::get('admin/login',  [Admin\Auth\AuthenticatedSessionController::class, 'create'])->name('admin.login');
    Route::post('admin/login', [Admin\Auth\AuthenticatedSessionController::class, 'store'])->name('admin.login.store');
});

Route::middleware('auth:admin')->group(function () {
    Route::post('admin/logout', [Admin\Auth\AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
});

/* =========================
   Teacher: login / logout（メール認証は不要）
   ========================= */
Route::middleware('guest:teacher')->group(function () {
    Route::get('teacher/login',  [Teacher\Auth\AuthenticatedSessionController::class, 'create'])->name('teacher.login');
    Route::post('teacher/login', [Teacher\Auth\AuthenticatedSessionController::class, 'store'])->name('teacher.login.store');
});

Route::middleware('auth:teacher')->group(function () {
    Route::post('teacher/logout', [Teacher\Auth\AuthenticatedSessionController::class, 'destroy'])->name('teacher.logout');
});

// 学生・保護者 共通の「検証リンク」(メール内URLが参照するデフォルト名)
Route::get('verify-email/{id}/{hash}',[VerifyEmailController::class,'__invoke'])
    ->middleware(['auth:student,guardian','signed','throttle:6,1'])
    ->name('verification.verify');

Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth:student,guardian', 'throttle:6,1'])
    ->name('verification.send');

