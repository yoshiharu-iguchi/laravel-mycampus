<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\Student;
use Illuminate\View\View;
use Illuminate\Http\Request;


class EnrollmentController extends Controller
{

    // 履修登録の一覧ページ
    // ポイント：
    // 画面の検索項目(科目/年度/開講期間/キーワード)を受け取り、条件に合うデータだけを絞り込み、件数サマリー用にtotalを数え1ページ分を取り出してビューに渡す
    public function index(Request $request)
    {
        // 1)画面から来た検索条件を受け取る(なければnull)
        $subjectId = $request->integer('subject_id');
        $year = $request->integer('year');
        $term = $request->string('term')->toString();
        $keyword = trim((string)$request->input('keyword'));

        // 2)開講期間は選択肢外の値がきたら無視
        $validTerms = ['前期','後期','通年'];
        // 開講期間が入力されていても、かつリストに入っていないなら無効にする
        if (!empty($term) && !in_array($term,$validTerms)){
            $term = null;
        }
        // 3)enrollments_countで履修登録者数を自動集計
        $query = Subject::query()->withCount('enrollments');

        // 4)条件があれば順に絞り込み
        if ($subjectId) {
            $query->where('id',$subjectId);

        }
        if ($year) {
            $query->where('year',$year);
        }
        if ($term) {
            $query->where('term',$term);
        }
        if ($keyword !== '') {
            // キーワード：まずは科目名・科目コードに対して部分一致
            $query->where(function ($q) use ($keyword) {
                $q->where('name_ja','like',"%{$keyword}%")
                  ->orWhere('subject_code','like',"%{$keyword}%");
            });
        }
        // 5)並び順とページング(検索条件はリンクに引き継ぐ)
        $rows = $query->orderBy('year')->orderBy('subject_code')->paginate(20)->appends($request->query());

        // 7)プルダウン用：科目一覧(名前順)
        $subjects = Subject::orderBy('name_ja')->get(['id','name_ja','name_en']);

        // 8)ビューへ
        return view('admin.enrollments.index',[
            'rows' => $rows,
            'subjects' => $subjects,
            'total' => $rows->total(),
            'keyword' => $keyword,
        ]);
        
    }
    // 科目別の履修登録一覧

    public function bySubject(Subject $subject){
        $enrollments = Enrollment::with(['student','subject'])
            ->where('subject_id',$subject->id)
            ->latest()
            ->paginate(20)
            ->appends(request()->query());

        return view('admin.enrollments.by_subject',compact('subject','enrollments'));
    }

    // 学生別の履修登録一覧

    public function byStudent(Student $student){
        $enrollments = $student->enrollments()
        ->with('subject')
        ->latest()
        ->paginate(20)
        ->appends(request()->query());

        return view('admin.enrollments.by_student',compact('student','enrollments'));
    }
}
