<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


// 教員の出席コントローラはクラス名が衝突しやすいので alias
use App\Http\Controllers\Teacher\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\Teacher\ProfileController as TeacherProfileController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\EnrollmentController as AdminEnrollmentController;
use App\Http\Controllers\Auth\VerifyEmailController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => view('welcome'));

require __DIR__.'/auth.php';

/* ───────────── 共通・認証系（簡易画面） ───────────── */

Route::get('/register', fn () => response('Student Register', 200))
    ->middleware('guest')->name('register');

// Route::get('/login', fn () => response('Login', 200))
//     ->middleware('guest')->name('login');
Route::get('/login', function () {
    foreach (['student','teacher','admin','guardian'] as $g) {
        if (auth($g)->check()) return redirect()->route('dashboard');
    }
    return view('auth.login-hub');
})->name('login');

Route::get('/forgot-password', fn () => response('Forgot Password', 200))
    ->middleware('guest')->name('password.request');

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => ['required','email']]);
    $status = Password::sendResetLink($request->only('email'));
    return $status === Password::RESET_LINK_SENT
        ? back()->with(['status' => __($status)])
        : back()->withErrors(['email' => __($status)]);
})->middleware(['guest','throttle:6,1'])->name('password.email');

Route::get('/reset-password/{token}', fn () => response('Reset Password Form', 200))
    ->middleware('guest')->name('password.reset');

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
})->middleware('guest')->name('password.store');

Route::get('/confirm-password', fn () => response('Confirm Password', 200))
    ->middleware('auth')->name('password.confirm');

Route::post('/confirm-password', function (Request $request) {
    $request->validate(['password' => ['required','string']]);
    $user = $request->user();
    if (! Hash::check($request->string('password'), $user->password)) {
        return back()->withErrors(['password' => __('auth.password')]);
    }
    $request->session()->put('auth.password_confirmed_at', time());
    return redirect()->intended('/');
})->middleware(['auth','throttle:6,1']);

Route::get('/verify-email', fn () => response('Verify Email Notice', 200))
    ->middleware('auth')->name('verification.notice');

Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['auth','signed','throttle:6,1'])
    ->name('verification.verify'); 

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status','verification-link-sent');
})->middleware(['auth','throttle:6,1'])->name('verification.send');

Route::get('/dashboard', function () {
    if (auth('student')->check())  return redirect()->route('student.home');
    if (auth('teacher')->check())  return redirect()->route('teacher.home');
    if (auth('admin')->check())    return redirect()->route('admin.home');
    if (auth('guardian')->check()) return redirect()->route('guardian.home');
    return redirect('/');
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

Route::middleware('auth')->match(['put','patch'], '/password', [\App\Http\Controllers\Auth\PasswordController::class,'update'])
    ->name('password.update');

/* ───────────── 学生 ───────────── */

Route::prefix('student')->as('student.')->middleware('auth:student')->group(function () {
    Route::get('email/verify',function () {
        return view('student.auth.verify-email');
    })->name('verification.notice');

    Route::get('email/verify/{id}/{hash}',function(EmailVerificationRequest $request) {
        $request->fulfill();

        /** @var \App\Models\Student $student */
        $student = $request->user('student');

        [$token,$expiresAt] = \App\Models\Student::issueGuardianToken(30);
        $student->forceFill([
            'guardian_registration_token' => $token,
        ])->save();

        $to = optional($student->guardian)->email ?: $student->email;
        \Illuminate\Support\Facades\Mail::to($to)
        ->send(new \App\Mail\GuardianInviteMail($student));

        return redirect()->route('student.home')
            ->with('status','メール認証が完了しました。保護者招待メールを送信しました。');
    })->middleware(['signed','throttle:6,1'])->name('verification.verify');

    Route::post('email/verification-notification',function (Request $request) {
        $request->user('student')->sendEmailVerificationNotification();
        return back()->with('status','認証メールを再送しました。');
    })->middleware(['throttle:6,1'])->name('verification.send');

    Route::get('home', [\App\Http\Controllers\Student\HomeController::class,'index'])->name('home');
    Route::get('profile', [\App\Http\Controllers\Student\ProfileController::class,'show'])->name('profile.show');

    Route::get('subjects', [\App\Http\Controllers\Student\SubjectController::class,'index'])->name('subjects.index');
    Route::get('subjects/{subject}', [\App\Http\Controllers\Student\SubjectController::class,'show'])->name('subjects.show');

    // 出席・成績（閲覧）
    Route::get('attendances', [\App\Http\Controllers\Student\AttendanceController::class,'index'])->name('attendances.index');
    Route::get('grades',      [\App\Http\Controllers\Student\GradeController::class,'index'])->name('grades.index');

    Route::get('enrollments', [\App\Http\Controllers\Student\EnrollmentController::class,'index'])->name('enrollments.index');
    Route::post('enrollments', [\App\Http\Controllers\Student\EnrollmentController::class,'store'])->name('enrollments.store');
    Route::delete('enrollments/{enrollment}', [\App\Http\Controllers\Student\EnrollmentController::class,'destroy'])->name('enrollments.destroy');

    Route::get('progress', [\App\Http\Controllers\Student\ProgressController::class,'index'])->name('progress.index');

    // 交通費申請
    Route::get('transport-requests', [\App\Http\Controllers\Student\TransportRequestController::class,'index'])->name('tr.index');
    Route::get('transport-requests/create', [\App\Http\Controllers\Student\TransportRequestController::class, 'create'])->name('tr.create');
    Route::post('transport-requests/search', [\App\Http\Controllers\Student\TransportRequestController::class,'search'])->name('tr.search');
    Route::post('transport-requests', [\App\Http\Controllers\Student\TransportRequestController::class, 'store'])->name('tr.store');
    Route::get('transport-requests/{tr}/flash',[\App\Http\Controllers\Admin\TransportRequestAdminController::class,'flash'])->name('tr.flash');

    // 施設一覧
    Route::get('facilities', [\App\Http\Controllers\Student\FacilityController::class,'index'])->name('facilities.index');
});

/* ───────────── 保護者（ログイン後） ───────────── */

Route::prefix('guardian')->as('guardian.')->middleware('auth:guardian')->group(function () {
    Route::get('home', [\App\Http\Controllers\Guardian\HomeController::class,'index'])->name('home');
    Route::get('profile', [\App\Http\Controllers\Guardian\ProfileController::class,'show'])->name('profile.show');
    Route::get('progress', [\App\Http\Controllers\Guardian\ProgressController::class,'index'])->name('progress.index');
    // Route::get('email/verify', fn () => response('Guardian Verify Email Notice',200))->name('verification.notice');

    // 出席・成績（閲覧：自分の子のみ）
    Route::get('attendances', [\App\Http\Controllers\Guardian\AttendanceController::class,'index'])->name('attendances.index');
    Route::get('grades',      [\App\Http\Controllers\Guardian\GradeController::class,'index'])->name('grades.index');
});

/* ───────────── 保護者：トークン登録（未ログイン） ───────────── */

Route::prefix('guardian')->as('guardian.')->middleware(['guest:guardian','throttle:30,1'])->group(function () {
    Route::get('register/{token}', [\App\Http\Controllers\Guardian\RegisterWithTokenController::class,'show'])
        ->where('token','[A-Za-z0-9]{64}')
        ->name('register.token.show');

    Route::post('register/{token}', [\App\Http\Controllers\Guardian\RegisterWithTokenController::class,'store'])
        ->where('token','[A-Za-z0-9]{64}')
        ->name('register.token.store');

    Route::get('register/complete', [\App\Http\Controllers\Guardian\RegisterWithTokenController::class,'complete'])
        ->name('register.complete');
});

/* ───────────── 管理者 ───────────── */

Route::prefix('admin')->as('admin.')->middleware('auth:admin')->group(function () {
    Route::redirect('/','dashboard');
    Route::get('dashboard', [\App\Http\Controllers\Admin\HomeController::class,'index'])->name('dashboard');
    Route::get('home', [\App\Http\Controllers\Admin\HomeController::class,'index'])->name('home');
    Route::get('profile',[AdminProfileController::class,'show'])->name('profile.show');

    Route::resource('facilities',\App\Http\Controllers\Admin\FacilityController::class)
        ->except(['show']);

    // Students
    Route::resource('students', \App\Http\Controllers\Admin\StudentController::class)
        ->only(['index','show','edit','update','destroy']);

    // Teachers（フルリソース）
    Route::resource('teachers', \App\Http\Controllers\Admin\TeacherController::class);

    // Subjects（フルリソース）
    Route::resource('subjects', \App\Http\Controllers\Admin\SubjectController::class);

    // Enrollments (閲覧)
    Route::controller(AdminEnrollmentController::class)
    ->prefix('enrollments')
    ->as('enrollments.')
    ->group(function () {
        Route::get('/', 'index')->name('index'); // admin.enrollments.index
        Route::get('/subject/{subject}', 'bySubject')
            ->whereNumber('subject')
            ->name('bySubject');                  // admin.enrollments.bySubject
        Route::get('/student/{student}', 'byStudent')
            ->whereNumber('student')
            ->name('byStudent');                  // admin.enrollments.byStudent
    }); 
    // Student invitation
    Route::post('students/{student}/invite', [\App\Http\Controllers\Admin\StudentInviteController::class,'send'])->name('students.invite');

    // Transport requests approve/reject
    Route::get('transport-requests', [\App\Http\Controllers\Admin\TransportRequestAdminController::class,'index'])->name('tr.index');
    Route::patch('transport-requests/{tr}/approve', [\App\Http\Controllers\Admin\TransportRequestAdminController::class,'approve'])->name('tr.approve');
    Route::patch('transport-requests/{tr}/reject', [\App\Http\Controllers\Admin\TransportRequestAdminController::class,'reject'])->name('tr.reject');
});

/* ───────────── 教員 ───────────── */

Route::prefix('teacher')->as('teacher.')->middleware('auth:teacher')->group(function () {
    Route::redirect('/','dashboard');
    Route::get('dashboard', [\App\Http\Controllers\Teacher\HomeController::class,'index'])->name('dashboard');
    Route::get('home', [\App\Http\Controllers\Teacher\HomeController::class,'index'])->name('home');

    Route::get('subjects', [\App\Http\Controllers\Teacher\SubjectController::class,'index'])->name('subjects.index');
    Route::get('subjects/{subject}', [\App\Http\Controllers\Teacher\SubjectController::class,'show'])
        ->whereNumber('subject')
        ->middleware('can:view,subject')
        ->name('subjects.show');

    Route::get('subjects/{subject}/enrollments', [\App\Http\Controllers\Teacher\EnrollmentController::class,'index'])
        ->whereNumber('subject')
        ->middleware('can:view,subject')
        ->name('enrollments.index');

    // 出席（教師用）
    Route::get('attendances', [TeacherAttendanceController::class,'index'])->name('attendances.index');
    Route::get('attendances/{subject}', [TeacherAttendanceController::class, 'index'])->whereNumber('subject')->name('attendances.bySubject');
    Route::post('attendances/{subject}/bulk-update', [TeacherAttendanceController::class,'bulkUpdate'])->whereNumber('subject')->name('attendances.bulkUpdate');
    // Route::post('attendances/bulk-update',[TeacherAttendanceController::class,'bulkUpdate'])->name('attendances.bulkUpdate');

    // 成績（教師用）
    Route::get('grades', [\App\Http\Controllers\Teacher\GradeController::class,'index'])->name('grades.index');
    Route::post('grades/bulk-update', [\App\Http\Controllers\Teacher\GradeController::class,'bulkUpdate'])->name('grades.bulkUpdate');

    Route::get('profile',[TeacherProfileController::class,'show'])->name('profile.show');
    Route::get('profile/edit',  [TeacherProfileController::class,'edit'])->name('profile.edit');
    Route::patch('profile',     [TeacherProfileController::class,'update'])->name('profile.update');
    Route::delete('profile',    [TeacherProfileController::class,'destroy'])->name('profile.destroy');

    
});