<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>学生詳細（管理）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h1 class="h4 mb-4">学生詳細</h1>

  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  <div class="card">
    <div class="card-body">
      <dl class="row">
        <dt class="col-sm-3">ID</dt>
        <dd class="col-sm-9">{{ $student->id }}</dd>

        <dt class="col-sm-3">氏名</dt>
        <dd class="col-sm-9">{{ $student->name }}</dd>

        <dt class="col-sm-3">学籍番号</dt>
        <dd class="col-sm-9">{{ $student->student_number }}</dd>

        <dt class="col-sm-3">メール</dt>
        <dd class="col-sm-9">{{ $student->email }}</dd>

        <dt class="col-sm-3">住所</dt>
        <dd class="col-sm-9">{{ $student->address ?? '未登録' }}</dd>

        <dt class="col-sm-3">登録日時</dt>
        <dd class="col-sm-9">{{ $student->created_at }}</dd>

        <dt class="col-sm-3">更新日時</dt>
        <dd class="col-sm-9">{{ $student->updated_at }}</dd>
      </dl>
    </div>
  </div>

  <div class="mt-3 d-flex justify-content-between">
    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">一覧に戻る</a>

    <div>
      <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-primary">編集</a>

      <form action="{{ route('admin.students.destroy', $student) }}" method="POST" class="d-inline" onsubmit="return confirm('本当に削除しますか？')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">削除</button>
      </form>
    </div>
  </div>

</div>
</body>
</html>