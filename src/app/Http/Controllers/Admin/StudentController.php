<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    // 検索ボックスに入力されたキーワードを取得する
    public function index(Request $request) {
        
        $keyword = trim((string) $request->input('keyword', ''));

    $students = Student::query()
        ->when($keyword !== '', function ($q) use ($keyword) {
            $q->where(function ($w) use ($keyword) {
                $w->where('name', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%")
                  ->orWhere('student_number', 'like', "%{$keyword}%");
            });
        })
        ->select('id','name','email','student_number','created_at') // 必要分だけ
        ->orderBy('id')
        ->paginate(15)
        ->appends(['keyword' => $keyword]); // ページング遷移で検索語を保持

    $total = $students->total();

    return view('admin.students.index', compact('students','keyword','total'));
    }

    public function show(Student $student) {

        $student->load('guardian');
        
        return view('admin.students.show',compact('student'));
    }

    public function edit(Student $student){
        return view('admin.students.edit',compact('student'));
    }

    public function update(Request $request,Student $student){
        $validated = $request->validate([
            'name' => ['required','string','max:255'],
            'student_number' => ['required','string','max:255',Rule::unique('students','student_number')->ignore($student->id), ],
            'email' => ['required','string','email:rfc','max:255',Rule::unique('students','email')->ignore($student->id),],
            'address' => ['nullable','string','max:255'],    
        ]);
        $student->update($validated);

        return redirect()->route('admin.students.show',$student)->with('status','学生情報を更新しました');
        
    }

    public function destroy(Student $student)
    {
        try{
            $student->delete();
            return redirect()
                    ->route('admin.students.index')
                    ->with('status','学生を削除しました');
        } catch (\Throwable $e) {
            return redirect()
                    ->route('admin.student.show',$student)
                    ->with('error','関連データがあるため削除できません。');
        }

    }
}