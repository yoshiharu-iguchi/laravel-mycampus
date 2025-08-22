<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>学生編集（管理）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h1 class="h4 mb-4">学生情報の編集</h1>

  {{-- バリデーションエラー表示 --}}
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.students.update', $student) }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label class="form-label">氏名</label>
      <input type="text" name="name" value="{{ old('name', $student->name) }}" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">学籍番号</label>
      <input type="text" name="student_number" value="{{ old('student_number', $student->student_number) }}" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">メールアドレス</label>
      <input type="email" name="email" value="{{ old('email', $student->email) }}" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">住所</label>
      <input type="text" name="address" value="{{ old('address', $student->address) }}" class="form-control">
    </div>

    <div class="d-flex justify-content-between">
      <a href="{{ route('admin.students.show', $student) }}" class="btn btn-outline-secondary">戻る</a>
      <button type="submit" class="btn btn-primary">更新する</button>
    </div>
  </form>

</div>
</body>
</html>