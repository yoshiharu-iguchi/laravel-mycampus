<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>科目詳細（学生）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Bootstrap（必要ならレイアウトへ移動可） --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h1 class="h4 mb-4">科目詳細</h1>

  {{-- フラッシュメッセージ --}}
  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  {{-- 科目情報カード --}}
  <div class="card mb-3">
    <div class="card-body">
      <dl class="row mb-0">
        <dt class="col-sm-3">ID</dt>
        <dd class="col-sm-9">{{ $subject->id }}</dd>

        <dt class="col-sm-3">科目コード</dt>
        <dd class="col-sm-9">{{ $subject->subject_code }}</dd>

        <dt class="col-sm-3">科目名</dt>
        <dd class="col-sm-9">{{ $subject->name_ja ?? $subject->name_en ?? '名称未設定' }}</dd>

        <dt class="col-sm-3">単位</dt>
        <dd class="col-sm-9">{{ rtrim(rtrim(number_format($subject->credits,1),'0'),'.') }}</dd>

        <dt class="col-sm-3">年度</dt>
        <dd class="col-sm-9">{{ $subject->year ?? '—' }}</dd>

        <dt class="col-sm-3">開講期間</dt>
        <dd class="col-sm-9">{{ $subject->term ?? '—' }}</dd>

        <dt class="col-sm-3">必修/選択</dt>
        <dd class="col-sm-9">
          @php
            $cat = $subject->category ?? null;
            $label = $cat==='required' ? '必修' : ($cat==='elective' ? '選択' : ($cat ?? '—'));
          @endphp
          {{ $label }}
        </dd>

        <dt class="col-sm-3">定員</dt>
        <dd class="col-sm-9">{{ $subject->capacity ?? '—' }}</dd>

        <dt class="col-sm-3">説明</dt>
        <dd class="col-sm-9">{{ $subject->description ?: '（説明は登録されていません）' }}</dd>
      </dl>
    </div>
  </div>

  {{-- 履修登録カード --}}
  <div class="card">
  <div class="card-header">履修手続き</div>
  <div class="card-body">
    @if($enrollment)
      {{-- ★履修中：取消ボタンのみ表示 --}}
      <form method="POST" action="{{ route('student.enrollments.destroy', $enrollment) }}"
            onsubmit="return confirm('この履修を取り消しますか？')">
        @csrf
        @method('DELETE')
        <input type="hidden" name="return_to" value="{{ url()->full() }}"> {{-- 戻り先を明示 --}}
        <button class="btn btn-outline-danger">履修を取り消す</button>
      </form>
      <span class="badge text-bg-success ms-2 align-middle">履修中</span>
    @else
      {{-- ★未履修：登録フォームを表示 --}}
      <form method="POST" action="{{ route('student.enrollments.store') }}" class="row g-2 align-items-center">
        @csrf
        <input type="hidden" name="return_to" value="{{ url()->full() }}"> {{-- 戻り先を明示 --}}
        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
        <input type="hidden" name="year" value="{{ now()->year }}">

        <div class="col-auto">
  <label for="term" class="col-form-label">学期</label>
</div>
<div class="col-auto">
  @php
    // 既存の値を安全に数値へ寄せる（編集/詳細ページでも落ちないように）
    $subjectTermValue = null;
    if ($subject->term instanceof \App\Enums\Term) {
        $subjectTermValue = $subject->term->value;
    } elseif (is_numeric($subject->term)) {
        $subjectTermValue = (int) $subject->term;
    } else {
        $subjectTermValue = null; // 不明なら null
    }
    // old('term') 優先、なければ科目の既存値、さらに無ければ「通年」を既定値に
    $currentTerm = (int) old('term', $subjectTermValue ?? \App\Enums\Term::FullYear->value);
  @endphp

  <select id="term" name="term" class="form-select">
    @foreach(\App\Enums\Term::cases() as $t)
      <option value="{{ $t->value }}" @selected($currentTerm === $t->value)>
        {{ $t->label() }}
      </option>
    @endforeach
  </select>
</div>
        <div class="col-auto">
          <button class="btn btn-primary">履修登録する</button>
        </div>
      </form>
    @endif
  </div>
</div>

  {{-- 戻るリンク --}}
  <div class="mt-3">
    <a href="{{ route('student.subjects.index') }}" class="btn btn-outline-secondary">科目一覧へ戻る</a>
  </div>

</div>
</body>
</html>
