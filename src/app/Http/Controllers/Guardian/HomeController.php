<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
{
    /** @var \App\Models\Guardian $guardian */
    $guardian = auth('guardian')->user();

    // 学生と科目をまとめて eager load
    $guardian->load(['student.subjects:id,name_ja,name_en']);

    $student = $guardian->student; // ← ここで Student モデル or null

    $firstSubject = $student?->subjects->first();
    $firstSubjectName = $firstSubject?->name_ja ?? $firstSubject?->name_en ?? null;

    return view('guardian.home', [
        'student'          => $student,
        'firstSubjectName' => $firstSubjectName,
    ]);
}
}
