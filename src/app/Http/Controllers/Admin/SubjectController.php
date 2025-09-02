<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    /**
     * 科目一覧（キーワードがあれば日本語名 or 科目コードで部分一致）
     */
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');

        $query = Subject::query();

        if (!empty($keyword)) {
            $like = "%{$keyword}%";
            $query->where(function ($q) use ($like) {
                $q->where('name_ja', 'like', $like)
                  ->orWhere('subject_code', 'like', $like);
            });
        }

        $subjects = $query->paginate(15);
        $subjects->appends($request->only('keyword'));
        $total = $subjects->total();

        return view('admin.subjects.index', compact('subjects', 'keyword', 'total'));
    }

    /**
     * 作成フォームに「学期」と「区分」の候補を渡す
     */
    public function create()
    {
        $terms = ['前期', '後期', '通年'];
        $categories = ['必修', '選択'];

        return view('admin.subjects.create', compact('terms', 'categories'));
    }

    /**
     * 登録処理（日本語でバリデーション項目名だけ指定）
     */
    public function store(Request $request)
    {
        $rules = [
            'subject_code' => ['required', 'string', 'max:30', 'unique:subjects,subject_code'],
            'name_ja'      => ['required', 'string', 'max:100'],
            'name_en'      => ['nullable', 'string', 'max:100'], // 英語名は任意（使わなくてもOK）
            'credits'      => ['required', 'numeric', 'between:0,99.9'], // 1.5 なども許可
            'year'         => ['nullable', 'integer', 'between:2000,2100'],
            'term'         => ['nullable', 'string', Rule::in(['前期','後期','通年'])],
            'category'     => ['required', 'string', Rule::in(['必修','選択'])],
            'capacity'     => ['nullable', 'integer', 'min:0', 'max:100000'],
            'description'  => ['nullable', 'string'],
        ];

        // エラーメッセージの「項目名」だけ日本語化（メッセージ本文は既定でOK）
        $attributes = [
            'subject_code' => '科目コード',
            'name_ja'      => '科目名（日本語）',
            'name_en'      => '科目名（英語）',
            'credits'      => '単位数',
            'year'         => '年度',
            'term'         => '開講期間',
            'category'     => '必修/選択',
            'capacity'     => '定員',
            'description'  => '説明',
        ];

        $validated = $request->validate($rules, [], $attributes);

        $subject = Subject::create($validated);

        return redirect()->route('admin.subjects.show', $subject)
                         ->with('status', '科目を登録しました。');
    }

    /**
     * 詳細表示
     */
    public function show(Subject $subject)
    {
        return view('admin.subjects.show', compact('subject'));
    }

    /**
     * 編集フォーム（作成時と同じ候補を渡す）
     */
    public function edit(Subject $subject)
    {
        $terms = ['前期', '後期', '通年'];
        $categories = ['必修', '選択'];

        return view('admin.subjects.edit', compact('subject', 'terms', 'categories'));
    }

    /**
     * 更新処理（ユニークは自分のIDを除外）
     */
    public function update(Request $request, Subject $subject)
    {
        $rules = [
            'subject_code' => [
                'required', 'string', 'max:30',
                Rule::unique('subjects', 'subject_code')->ignore($subject->id),
            ],
            'name_ja'      => ['required', 'string', 'max:100'],
            'name_en'      => ['nullable', 'string', 'max:100'],
            'credits'      => ['required', 'numeric', 'between:0,99.9'],
            'year'         => ['nullable', 'integer', 'between:2000,2100'],
            'term'         => ['nullable', 'string', Rule::in(['前期','後期','通年'])],
            'category'     => ['required', 'string', Rule::in(['必修','選択'])],
            'capacity'     => ['nullable', 'integer', 'min:0', 'max:100000'],
            'description'  => ['nullable', 'string'],
        ];

        $attributes = [
            'subject_code' => '科目コード',
            'name_ja'      => '科目名（日本語）',
            'name_en'      => '科目名（英語）',
            'credits'      => '単位数',
            'year'         => '年度',
            'term'         => '学期',
            'category'     => '区分',
            'capacity'     => '定員',
            'description'  => '説明',
        ];

        $validated = $request->validate($rules, [], $attributes);

        $subject->update($validated);

        return redirect()->route('admin.subjects.show', $subject)
                         ->with('status', '科目を更新しました。');
    }

    /**
     * 削除
     */
    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()->route('admin.subjects.index')
                         ->with('status', '科目を削除しました。');
    }
}

