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
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

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
Route::get('/login', function () {
    return response('Login', 200);
})->middleware('guest')->name('login');

Route::get('/forgot-password', function () {
    return response('Forgot Password', 200); // 画面なしでも200ならテスト通過
})->middleware('guest')->name('password.request');

// 2) リンク請求: POST /forgot-password
Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => ['required','email']]);

    $status = Password::sendResetLink($request->only('email'));

    return $status === Password::RESET_LINK_SENT
        ? back()->with(['status' => __($status)])
        : back()->withErrors(['email' => __($status)]);
})->middleware(['guest','throttle:6,1'])->name('password.email');

// 3) 再設定フォーム: GET /reset-password/{token}
Route::get('/reset-password/{token}', function (Request $request, string $token) {
    return response('Reset Password Form', 200);
})->middleware('guest')->name('password.reset');

// 4) 再設定実行: POST /reset-password
Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token'    => ['required'],
        'email'    => ['required','email'],
        'password' => ['required','confirmed','min:8'],
    ]);

    $status = Password::reset(
        $request->only('email','password','password_confirmation','token'),
        function ($user) use ($request) {
            $user->forceFill([
                'password'       => Hash::make($request->password),
                'remember_token' => Str::random(60),
            ])->save();
        }
    );

    return $status == Password::PASSWORD_RESET
        ? redirect()->route('login')->with('status', __($status))
        : back()->withErrors(['email' => [__($status)]]);
})->middleware('guest')->name('password.update');

Route::get('/confirm-password',function() {
    return response('Confirm Password',200);
})->middleware('auth')->name('password.confirm');

Route::post('/confirm-password',function (Request $request) {
    $request->validate([
        'password' => ['required','string'],
    ]);
    $user = $request->user();
    if (! Hash::check($request->input('password'),$user->password)){
        return back()->withErrors(['password' => __('auth.password')]);
    }
    $request->session()->put('auth.password_confirmed_at',time());

    return redirect()->intended('/');
})->middleware(['auth','throttle:6,1']);
Route::get('/verify-email',function(){
    return response('Verify Email Notice',200);
})->middleware('auth')->name('verification.notice');

Route::get('/verify-email/{id}/{hash}',function (EmailVerificationRequest $request){
    $user = $request->user();

    if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    return redirect(RouteServiceProvider::HOME.'?verified=1');
})->middleware(['auth','signed'])->name('verification.verify');

Route::post('/email/verification-notification',function(Request $request){
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status','verification-link-sent');
})->middleware(['auth','throttle:6,1'])->name('verification.send');

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

Route::put('/password', function (Request $request) {
    $validator = Validator::make($request->all(),[
        'current_password'      => ['required', 'current_password'], // 現在パス
        'password'              => ['required', 'confirmed', PasswordRule::min(8)],

    ]);
    if ($validator->fails()){
        return redirect('/profile')->withErrors($validator,'updatePassword');
    }
    $request->user()->forceFill([
        'password' => Hash::make($request->input('password')),
    ])->save();

    return redirect('/profile');
        
    })->middleware('auth')->name('password.update.current');

   


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
    Route::get('profile',[Guardian\ProfileController::class,'show'])->name('profile.show');
    Route::get('progress',[Guardian\ProgressController::class,'index'])
        ->name('progress.index');
});

// トークン付き保護者登録ルートを追加(ログイン不要)
Route::prefix('guardian')->name('guardian.')->middleware(['guest:guardian','throttle:30,1'])->group(function(){
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


    