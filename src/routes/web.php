<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Student;
use App\Http\Controllers\Guardian;
use App\Http\Controllers\Teacher;
use App\Http\Controllers\Guardian\RegisterWithTokenController;
use App\Http\Controllers\Admin\StudentInviteController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/',function() {
    return view('welcome');
});

require __DIR__.'/auth.php';

//学生ルート
Route::group(['prefix' => 'student', 'as' => 'student.', 'middleware' => 'auth:student'], function() {
    Route::get('home', [Student\HomeController::class,'index'])->name('home');
});

//保護者ルート
Route::group(['prefix' => 'guardian','as' => 'guardian.', 'middleware' => 'auth:guardian'],function(){
    Route::get('home',[Guardian\HomeController::class,'index'])->name('home');
});
// トークン付き保護者登録ルートを追加(ログイン不要)
Route::prefix('guardians')->name('guardian.')->middleware(['guest:guardian','throttle:30,1'])->group(function(){
    Route::get('register/complete',[RegisterWithTokenController::class,'complete'])
        ->name('register.complete');

    Route::get('register/{token}',[RegisterWithTokenController::class,'show'])
        ->where('token','[A-Fa-f0-9]{64}')
        ->name('register.token.show');

    Route::post('register/{token}',[RegisterWithTokenController::class,'store'])
        ->where('token','[A-Fa-f0-9]{64}')
        ->name('register.token.store');
});



// 管理者ルート
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth:admin'],function(){
    Route::get('home',[Admin\HomeController::class,'index'])->name('home');

    Route::resource('students',Admin\StudentController::class)->only(['index','show','edit','update','destroy']);
    Route::resource('teachers',Admin\TeacherController::class);

    // 学生に招待メールを再送(POST)
    Route::post('students/{student}/invite',[Admin\StudentInviteController::class,'send'])
        ->name('students.invite');
});

// 教員ルート
Route::group(['prefix' => 'teacher', 'as' => 'teacher.', 'middleware' => 'auth:teacher'],function(){
    Route::get('home',[Teacher\HomeController::class,'index'])->name('home');
});


    