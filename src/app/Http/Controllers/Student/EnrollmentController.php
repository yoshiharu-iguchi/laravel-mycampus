<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreEnrollmentRequest;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\Student; // ← 追加（型コメント用）
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function index(): View
    {
        /** @var Student $student */   // ← これで赤線が消えます
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
        $year    = (int) $request->input('year');
        $term    = $request->input('term');

        $enrollment = Enrollment::firstOrCreate(
            [
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'year'       => $year,
                'term'       => $term,
            ],
            [
                'status'        => 'registered',
                'registered_at' => now(),
            ]
        );
        $message = $enrollment->wasRecentlyCreated ? '履修登録しました':'既に履修済みです';

        return back()->with('status',$message);
            
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
