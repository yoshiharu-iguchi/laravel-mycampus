<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>科目 詳細（管理）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Bootstrap --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h1 class="h4 mb-4">科目 詳細</h1>

  {{-- フラッシュメッセージ（任意） --}}
  <!-- @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif -->

  <div class="card">
    <div class="card-body">
      <dl class="row mb-0">
        <dt class="col-sm-3">科目コード</dt>
        <dd class="col-sm-9">{{ $subject->subject_code ?? '—' }}</dd>

        <dt class="col-sm-3">科目名（日本語）</dt>
        <dd class="col-sm-9">{{ $subject->name_ja ?? '—' }}</dd>

        <dt class="col-sm-3">科目名（英語）</dt>
        <dd class="col-sm-9">{{ $subject->name_en ?? '—' }}</dd>

        <dt class="col-sm-3">単位</dt>
        <dd class="col-sm-9">{{ $subject->credits ?? '—' }}</dd>

        <dt class="col-sm-3">年度</dt>
        <dd class="col-sm-9">{{ $subject->year ?? '—' }}</dd>

        <dt class="col-sm-3">開講期間</dt>
        <dd class="col-sm-9">{{ $subject->term_label ?? '—' }}</dd>

        <dt class="col-sm-3">必修/選択</dt>
        <dd class="col-sm-9">{{ $subject->category_label ?? '—' }}</dd>

        <dt class="col-sm-3">定員</dt>
        <dd class="col-sm-9">{{ $subject->capacity ?? '—' }}</dd>

        <dt class="col-sm-3">概要</dt>
        <dd class="col-sm-9">{!! nl2br(e($subject->description ?? '—')) !!}</dd>

        <dt class="col-sm-3">履修者数</dt>
        <dd class="col-sm-9">{{ $subject->enrollments_count ?? $subject->enrollments()->count() }} 名</dd>
      </dl>

      <div class="mt-3 d-flex flex-wrap gap-2">
        <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-primary">編集する</a>

        @if(Route::has('admin.enrollments.by_subject'))
          <a href="{{ route('admin.enrollments.by_subject', ['subject' => $subject->id]) }}"
             class="btn btn-outline-secondary">
            履修登録者一覧へ
          </a>
        @endif

        <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary">一覧へ戻る</a>
      </div>
    </div>
  </div>

</div>
</body>
</html>
