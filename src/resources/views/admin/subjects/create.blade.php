<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>科目 新規登録（管理）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Bootstrap --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h1 class="h4 mb-4">科目 新規登録</h1>

  {{-- フラッシュメッセージ --}}
  <!-- @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif -->

  {{-- バリデーションエラー --}}
  @if ($errors->any())
    <div class="alert alert-danger">
      <div class="fw-bold mb-1">入力内容に誤りがあります。修正してください。</div>
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.subjects.store') }}" class="row g-3">
        @csrf

        <div class="col-md-4">
          <label class="form-label">科目コード</label>
          <input type="text" name="subject_code" value="{{ old('subject_code') }}" class="form-control" placeholder="例：OT101" required>
        </div>

        <div class="col-md-8">
          <label class="form-label">科目名（日本語）</label>
          <input type="text" name="name_ja" value="{{ old('name_ja') }}" class="form-control" placeholder="例：作業療法概論" required>
        </div>

        <div class="col-md-8">
          <label class="form-label">科目名（英語）</label>
          <input type="text" name="name_en" value="{{ old('name_en') }}" class="form-control" placeholder="例：Introduction to Occupational Therapy">
        </div>

        <div class="col-md-4">
          <label class="form-label">単位</label>
          <input type="number" step="0.5" min="0" name="credits" value="{{ old('credits', 1.0) }}" class="form-control" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">年度</label>
          <input type="number" name="year" value="{{ old('year') }}" class="form-control" placeholder="例：2025">
        </div>

        <div class="col-md-4">
          <label class="form-label">開講期間</label>
          <select name="term" class="form-select">
            <option value="" @selected(old('term') === null)>未選択</option>
            @foreach (['前期','後期','通年'] as $t)
              <option value="{{ $t }}" @selected(old('term')===$t)>{{ $t }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">必修/選択</label>
          <select name="category" class="form-select" required>
            @foreach (['required'=>'必修','elective'=>'選択'] as $val=>$label)
              <option value="{{ $val }}" @selected(old('category','elective')===$val)>{{ $label }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">定員</label>
          <input type="number" min="0" name="capacity" value="{{ old('capacity') }}" class="form-control" placeholder="例：60">
        </div>

        <div class="col-12">
          <label class="form-label">概要</label>
          <textarea name="description" rows="4" class="form-control" placeholder="科目の概要や到達目標など">{{ old('description') }}</textarea>
        </div>

        <div class="col-12 d-flex gap-2 mt-2">
          <button type="submit" class="btn btn-primary">登録する</button>
          <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">一覧に戻る</a>
        </div>
      </form>
    </div>
  </div>

</div>
</body>
</html>