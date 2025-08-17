<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Student;

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
// 管理者ルート
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth:admin'],function(){
    Route::get('home',[Admin\HomeController::class,'index'])->name('home');
});


    