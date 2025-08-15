<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student;
use App\Http\Controllers\Guardian;
use App\Http\Controllers\Teacher;
use App\Http\Controllers\Admins;

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

// 学生用ルート
Route::group(['prefix' => 'student', 'as' => 'student.', 'middleware' => 'auth:student'],function(){
    Route::get('home',[Student\HomeController::class,'index'])->name('home');
});

// 保護者ルート
Route::group(['prefix' => 'guardian', 'as' => 'guardian.', 'middleware' => 'auth:guardian'],function() {
    Route::get('home',[Guardian\HomeController::class,'index'])->name('home');
});

// 教員ルート
Route::group(['prefix' => 'teacher', 'as' => 'teacher.', 'middleware' => 'auth:teacher'], function(){
    Route::get('home',[Teacher\HomeController::class,'index'])->name('home');
});

// 管理者ルート
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth:admin'],function(){
    Route::get('home',[Admin\HomeController::class,'index'])->name('home');
});


    