<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');

        if ($keyword) {
            $subjects = Subject::where('name_ja','like',"%{$keyword}%")->paginate(15);
        } else {
            $subjects = Subject::paginate(15);
        }

        $total = $subjects->total();

        return view('admin.subjects.index',compact('subjects','keyword','total'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $terms = ['前期','後期','通年'];
        $categories = ['required','elective'];

        return view('admin.subjects.create',compact('terms','categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_code' => ['required','string','max:30','unique:subjects,subject_code'],
            'name_ja'      => ['required','string','max:100'],
            'name_en'      => ['nullable','string','max:100'],
            'credits'      => ['required','numeric','between:0,99.9'], // 1.5など想定
            'year'         => ['nullable','integer','between:2000,2100'],
            'term'         => ['nullable','string', Rule::in(['前期','後期','通年'])],
            'category'     => ['required','string', Rule::in(['required','elective'])],
            'capacity'     => ['nullable','integer','min:0','max:100000'],
            'description'  => ['nullable','string'],

        ]);
        $subject = Subject::create($validated);

        return redirect()->route('admin.subjects.show',$subject)->with('status','科目を登録しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(Subject $subject)
    {
        return view('admin.subjects.show',compact('subject'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subject $subject)
    {
        $terms = ['前期','後期','通年'];
        $categories = ['required','elective'];

        return view('admin.subjects.edit',compact('subject','terms','categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'subject_code' => [
                'required','string','max:30',Rule::unique('subjects','subject_code')->ignore($subject->id),
            ],
            'name_ja'      => ['required','string','max:100'],
            'name_en'      => ['nullable','string','max:100'],
            'credits'      => ['required','numeric','between:0,99.9'],
            'year'         => ['nullable','integer','between:2000,2100'],
            'term'         => ['nullable','string', Rule::in(['前期','後期','通年'])],
            'category'     => ['required','string', Rule::in(['required','elective'])],
            'capacity'     => ['nullable','integer','min:0','max:100000'],
            'description'  => ['nullable','string'],

        ]);
        $subject->update($validated);

        return redirect()->route('admin.subjects.show',$subject)->with('status','科目を更新しました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()->route('admin.subjects.index')->with('status','科目を削除しました。');
    }
}
