<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>教員詳細（管理）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h1 class="h4 mb-4">教員詳細</h1>

  {{-- フラッシュメッセージ --}}
  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  <div class="card">
    <div class="card-body">
      <dl class="row mb-0">
        <dt class="col-sm-3 text-muted">ID</dt>
        <dd class="col-sm-9">{{ $teacher->id }}</dd>

        <dt class="col-sm-3 text-muted">氏名</dt>
        <dd class="col-sm-9">{{ $teacher->name }}</dd>

        @if(isset($teacher->teacher_number))
          <dt class="col-sm-3 text-muted">教員番号</dt>
          <dd class="col-sm-9">{{ $teacher->teacher_number }}</dd>
        @endif

        <dt class="col-sm-3 text-muted">メールアドレス</dt>
        <dd class="col-sm-9">{{ $teacher->email }}</dd>

        <dt class="col-sm-3 text-muted">作成日時</dt>
        <dd class="col-sm-9">{{ $teacher->created_at }}</dd>

        <dt class="col-sm-3 text-muted">更新日時</dt>
        <dd class="col-sm-9">{{ $teacher->updated_at }}</dd>
      </dl>
    </div>
  </div>

  <div class="d-flex gap-2 mt-3">
    <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-secondary">一覧へ戻る</a>
    <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn btn-primary">編集</a>

    {{-- 削除 --}}
    <form method="POST" action="{{ route('admin.teachers.destroy', $teacher) }}" onsubmit="return confirm('この教員を削除します。よろしいですか？');">
      @csrf
      @method('DELETE')
      <button type="submit" class="btn btn-outline-danger">削除</button>
    </form>
  </div>

</div>
</body>
</html>