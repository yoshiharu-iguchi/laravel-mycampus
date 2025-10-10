<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;
use App\Enums\Category;


class SubjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }
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
        Log::info('ENTER store');

        $validated=$request->validate([
            'subject_code' => ['required', 'string', 'max:30', 'unique:subjects,subject_code'],
            'name_ja'      => ['required', 'string', 'max:100'],
            'name_en'      => ['nullable', 'string', 'max:100'], // 英語名は任意（使わなくてもOK）
            'credits'      => ['required', 'integer', 'between:1,4'], // 1.5 なども許可
            'year'         => ['nullable', 'integer', 'between:2000,2100'],
            'term'         => ['nullable', 'string', Rule::in(['前期','後期','通年',1,2,3,'1','2','3'])],
            'category'     => ['required', new Enum(Category::class)],
            'capacity'     => ['nullable', 'integer', 'min:0', 'max:100000'],
            'description'  => ['nullable', 'string'],
        ],[],[
            'subject_code' => '科目コード',
            'name_ja'      => '科目名（日本語）',
            'credits'      => '単位数',
            'year'         => '年度',
            'term'         => '開講期間',
            'category'     => '必修/選択',
            'capacity'     => '定員',

        ]);

        Log::info('VALIDATED',$validated);

        $subject = Subject::create($validated);

        Log::info('CREATED',['id' => $subject->id]);

        return redirect()->route('admin.subjects.show',$subject)
                        ->with('status','科目を登録しました。');
        
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
    // 1) バリデーション：テストの入力に合わせて必須 & Enum
    $validated = $request->validate([
        'subject_code' => [
            'required','string','max:30',
            Rule::unique('subjects', 'subject_code')->ignore($subject->id),
        ],
        'name_ja'      => ['required','string','max:100'],
        'name_en'      => ['nullable','string','max:100'],
        // credits が decimal カラムなら 'numeric' にする（int列なら integer のままでOK）
        'credits'      => ['required','numeric','between:0,99.9'],
        'year'         => ['required','integer','between:2000,2100'],
        // term は現状「前期/後期/通年」の文字列をDBに保存している前提
        'term'         => ['required', Rule::in(['前期','後期','通年'])],
        // category は Enum バリデーション（backing value は 'required' / 'elective'）
        'category'     => ['required', new Enum(Category::class)],
        'capacity'     => ['required','integer','min:0','max:100000'],
        'description'  => ['nullable','string'],
    ], [], [
        'subject_code' => '科目コード',
        'name_ja'      => '科目名（日本語）',
        'credits'      => '単位数',
        'year'         => '年度',
        'term'         => '開講期間',
        'category'     => '必修/選択',
        'capacity'     => '定員',
    ]);

    // 2) 念のため category を英語の backing value に正規化（画面から日本語が来ても吸収）
    if (!in_array($validated['category'], [Category::Required->value, Category::Elective->value], true)) {
        $validated['category'] = Category::fromLabel($validated['category'])->value;
    }

    // 3) mass assign の取りこぼしをゼロに（forceFill→save）
    $subject->forceFill($validated)->save();

    return redirect()->route('admin.subjects.show', $subject->id);
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

