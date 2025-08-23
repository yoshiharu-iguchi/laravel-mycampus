<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>教員新規登録（管理）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h1 class="h4 mb-4">教員新規登録</h1>

  {{-- バリデーションエラー --}}
  @if ($errors->any())
    <div class="alert alert-danger">
      <div class="fw-bold mb-1">入力内容を確認してください。</div>
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.teachers.store') }}" class="row g-3">
        @csrf

        <div class="col-md-6">
          <label for="name" class="form-label">氏名 <span class="text-danger">*</span></label>
          <input id="name" type="text" name="name" value="{{ old('name') }}" class="form-control" required>
        </div>

        {{-- 教員番号があるプロジェクトの場合のみ利用 --}}
        @php $useTeacherNumber = old('teacher_number') !== null || Schema::hasColumn('teachers','teacher_number'); @endphp
        @if($useTeacherNumber)
          <div class="col-md-6">
            <label for="teacher_number" class="form-label">教員番号</label>
            <input id="teacher_number" type="text" name="teacher_number" value="{{ old('teacher_number') }}" class="form-control">
          </div>
        @endif

        <div class="col-md-6">
          <label for="email" class="form-label">メールアドレス <span class="text-danger">*</span></label>
          <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" required>
        </div>

        <div class="col-md-6">
          <label for="password" class="form-label">初期パスワード <span class="text-danger">*</span></label>
          <input id="password" type="password" name="password" class="form-control" required>
          <div class="form-text">管理者が設定して教員へ通知します。</div>
        </div>

        <div class="col-12 d-flex gap-2">
          <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-secondary">キャンセル</a>
          <button type="submit" class="btn btn-primary">登録する</button>
        </div>
      </form>
    </div>
  </div>

</div>
</body>
</html>