<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Student;
use App\Http\Controllers\Guardian;
use App\Http\Controllers\Teacher;

use App\Http\Controllers\Guardian\RegisterWithTokenController;
use App\Http\Controllers\Admin\EnrollmentController as AdminEnrollmentController;
use App\Http\Controllers\Teacher\EnrollmentController as TeacherEnrollmentController;
use App\Http\Controllers\Student\TransportRequestController;
use App\Http\Controllers\Admin\TransportRequestAdminController;


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
Route::get('/dashboard', function () {
    if (auth('student')->check())  return redirect()->route('student.home');
    if (auth('teacher')->check())  return redirect()->route('teacher.home');
    if (auth('admin')->check())    return redirect()->route('admin.home');
    if (auth('guardian')->check()) return redirect()->route('guardian.home');
    return redirect('/'); // 未ログインなど
})->name('dashboard');

Route::get('/profile', fn () => redirect()->route('dashboard'))->name('profile.edit');

Route::post('/logout', function () {
    foreach (['student','teacher','admin','guardian','web'] as $guard) {
        if (auth($guard)->check()) { auth($guard)->logout(); }
    }
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

//学生ルート
Route::group(['prefix' => 'student', 'as' => 'student.', 'middleware' => 'auth:student'], function() {
    Route::get('home', [Student\HomeController::class,'index'])->name('home');
    Route::get('profile',[Student\ProfileController::class,'show'])->name('profile.show');

    // 科目一覧・詳細
    Route::get('subjects',[Student\SubjectController::class,'index'])->name('subjects.index');
    Route::get('subjects/{subject}',[Student\SubjectController::class,'show'])->name('subjects.show');

    // 履修一覧/登録/取り消し
    Route::get('enrollments',[Student\EnrollmentController::class,'index'])->name('enrollments.index');
    Route::post('enrollments',[Student\EnrollmentController::class,'store'])->name('enrollments.store');
    Route::delete('enrollments/{enrollment}',[Student\EnrollmentController::class,'destroy'])->name('enrollments.destroy');

    Route::get('progress',[Student\ProgressController::class,'index'])
        ->name('progress.index');

    // 学生の交通費申請
    Route::get('transport-requests',[TransportRequestController::class,'index'])->name('tr.index');
    Route::get('transport-requests/create', [TransportRequestController::class, 'create'])->name('tr.create');
    Route::post('transport-requests/search',[TransportRequestController::class,'search'])->name('tr.search');
    Route::post('transport-requests', [TransportRequestController::class, 'store'])->name('tr.store');

    Route::get('facilities',[Student\FacilityController::class,'index'])->name('facilities.index');
});

//保護者ルート
Route::group(['prefix' => 'guardian','as' => 'guardian.', 'middleware' => 'auth:guardian'],function(){
    Route::get('home',[Guardian\HomeController::class,'index'])->name('home');

    Route::get('progress',[Guardian\ProgressController::class,'index'])
        ->name('progress.index');

});
// トークン付き保護者登録ルートを追加(ログイン不要)
Route::prefix('guardians')->name('guardian.')->middleware(['guest:guardian','throttle:30,1'])->group(function(){
    Route::get('register/{token}',[RegisterWithTokenController::class,'show'])
        ->where('token','[A-Za-z0-9]{64}')
        ->name('register.token.show');

    Route::post('register/{token}',[RegisterWithTokenController::class,'store'])
        ->where('token','[A-Za-z0-9]{64}')
        ->name('register.token.store');

    Route::get('register/complete',[RegisterWithTokenController::class,'complete'])
        ->name('register.complete');
});



// 管理者ルート
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth:admin'],function(){
    // admin→/admin/dashboardにリダイレクト(フロア入り口の案内)
    Route::redirect('/','dashboard');
    Route::get('dashboard',[Admin\HomeController::class,'index'])->name('dashboard');

    Route::get('home',[Admin\HomeController::class,'index'])->name('home');

    Route::resource('students',Admin\StudentController::class)->only(['index','show','edit','update','destroy']);
    Route::resource('teachers',Admin\TeacherController::class);
    Route::resource('subjects',Admin\SubjectController::class);

    // 履修閲覧(管理機能)
    Route::get('enrollments',[AdminEnrollmentController::class,'index'])->name('enrollments.index');
    Route::get('enrollments/subject/{subject}',[AdminEnrollmentController::class,'bySubject'])->name('enrollments.bySubject');
    Route::get('enrollments/student/{student}',[AdminEnrollmentController::class,'byStudent'])->name('enrollments.byStudent');

    // 学生に招待メールを再送(POST)
    Route::post('students/{student}/invite',[Admin\StudentInviteController::class,'send'])
        ->name('students.invite');

    // 管理者交通費承認
    Route::get('transport-requests',[TransportRequestAdminController::class,'index'])->name('tr.index');
    Route::patch('transport-requests/{tr}/approve',[TransportRequestAdminController::class,'approve'])->name('tr.approve');
    Route::patch('transport-requests/{tr}/reject',[TransportRequestAdminController::class,'reject'])->name('tr.reject');
});

// 教員ルート
Route::group(['prefix' => 'teacher', 'as' => 'teacher.', 'middleware' => 'auth:teacher'],function(){
    // teacher->/teacher/dashboardにリダイレクト
    Route::redirect('/','dashboard');
    // ダッシュボード別名を追加
    Route::get('dashboard',[Teacher\HomeController::class,'index'])->name('dashboard');
    Route::get('home',[Teacher\HomeController::class,'index'])->name('home');

    // 科目一覧(自分の担当だけ表示)
    Route::get('subjects',[Teacher\SubjectController::class,'index'])
        ->name('subjects.index');
    // 科目詳細(ここを入り口に下位へ降りる)
    Route::get('subjects/{subject}',[Teacher\SubjectController::class,'show'])
        ->whereNumber('subject')
        ->middleware('can:view,subject')
        ->name('subjects.show');
    
    Route::get('subjects/{subject}/enrollments',[TeacherEnrollmentController::class,'index'])
        ->whereNumber('subject')
        ->middleware('can:view,subject')
        ->name('enrollments.index');

    // 出席(閲覧・一括更新)
    Route::get('subjects/{subject}/attendances',[Teacher\AttendanceController::class,'index'])
        ->whereNumber('subject')
        ->middleware('can:view,subject')
        ->name('attendances.index');

    Route::post('subjects/{subject}/attendances/bulk-update',[Teacher\AttendanceController::class,'bulkUpdate'])
        ->whereNumber('subject')
        ->middleware('can:view,subject')
        ->name('attendances.bulkUpdate');

    // 成績(閲覧・一括更新)
    Route::get('subjects/{subject}/grades',[Teacher\GradeController::class,'index'])
        ->whereNumber('subject')
        ->middleware('can:view,subject')
        ->name('grades.index');

    Route::post('subjects/{subject}/grades/bulk-update',[Teacher\GradeController::class,'bulkUpdate'])
        ->whereNumber('subject')
        ->middleware('can:view,subject')
        ->name('grades.bulkUpdate');
});


    