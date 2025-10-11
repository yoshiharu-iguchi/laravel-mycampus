<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreEnrollmentRequest;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\Student; // 型ヒント用
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Enums\Term;
use App\Enums\EnrollmentStatus;

class EnrollmentController extends Controller
{
    public function index(): View
    {
        /** @var Student $student */
        $student = auth('student')->user();

        $enrollments = $student->enrollments()
            ->with('subject')
            ->latest()
            ->paginate(20);

        return view('student.enrollments.index', compact('enrollments'));
    }

    public function store(StoreEnrollmentRequest $request): RedirectResponse
    {
        /** @var Student $student */
        $student = auth('student')->user();

        $subject = Subject::findOrFail($request->integer('subject_id'));

        // 年度：未送信なら科目の年度 or 今年
        $year = $request->integer('year') ?: (int)($subject->year ?? now()->year);

        $raw = (string)$request->input('term');
        $termEnum = match ($raw) {
            '前期','1' => \App\Enums\Term::First,
            '後期','2' => \App\Enums\Term::Second,
            '通年','3' => \App\Enums\Term::FullYear,
            default => null,
        };
        if (!$termEnum) {
            return redirect()->back()->withErrors(['default' => '学期の選択が正しくありません'])->withInput();
        }

        // （任意）科目の学期制約と整合させたい場合
        $subjectTermRaw = (string)($subject->term ?? '');
        $map = [
            '前期' => \App\Enums\Term::First,
            '後期' => \App\Enums\Term::Second,
            '通年' => \App\Enums\Term::FullYear,
            '1'    => \App\Enums\Term::First,
            '2'    => \App\Enums\Term::Second,
            '3'    => \App\Enums\Term::FullYear,
        ];

        $enrollment = Enrollment::firstOrCreate(
            [
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'year'       => $year,
                'term'       => $termEnum->value, // DBがintなら value を保存
            ],
            [
                'status'        => EnrollmentStatus::Registered,
                'registered_at' => now(),
            ]
        );

        return redirect()->route('student.enrollments.index')
            ->with('status',$enrollment->wasRecentlyCreated ? '履修登録しました':'既に履修済みです');
          
    }

    public function destroy(Enrollment $enrollment): RedirectResponse
    {
        /** @var Student $student */
        $student = auth('student')->user();

        abort_unless($enrollment->student_id === $student->id, 403);

        $enrollment->delete();

        return back()->with('status', '履修を取り消しました');
    }
}

