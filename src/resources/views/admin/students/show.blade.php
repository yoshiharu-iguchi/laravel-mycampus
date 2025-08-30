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
  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  {{-- 学生情報カード --}}
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

  {{-- ★ 保護者登録カード（ここに移動） --}}
  <div class="card mt-3">
    <div class="card-header">保護者登録</div>
    <div class="card-body">
      @if($student->guardian_registered_at)
        <p class="mb-2">
          状態: <span class="badge bg-success">登録済み</span>
        </p>
        <dl class="row">
          <dt class="col-sm-3">登録日時</dt>
          <dd class="col-sm-9">{{ $student->guardian_registered_at?->format('Y-m-d H:i') }}</dd>
        </dl>
      @else
        <p class="mb-2">
          状態: <span class="badge bg-warning text-dark">未登録</span>
        </p>

        @if($student->guardian_registration_token)
          <p class="small text-muted mb-1">招待URL</p>
          <div class="input-group mb-3">
            <input type="text" class="form-control" readonly
                   value="{{ route('guardian.register.token.show', ['token' => $student->guardian_registration_token]) }}">
            <button class="btn btn-outline-secondary" type="button"
                    onclick="navigator.clipboard.writeText(this.previousElementSibling.value)">
              コピー
            </button>
          </div>
        @else
          <p class="text-muted">現在トークンがありません（再招待すると自動発行されます）。</p>
        @endif

        <form method="POST" action="{{ route('admin.students.invite', $student) }}" class="d-inline">
          @csrf
          <button type="submit" class="btn btn-primary">
            保護者招待メールを再送
          </button>
        </form>
      @endif
    </div>
  </div>

  {{-- 戻る・編集・削除ボタン群 --}}
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