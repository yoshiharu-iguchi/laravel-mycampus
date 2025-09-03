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

  <h1 class="h4 mb-3">学生編集</h1>

  {{-- バリデーションエラー（全体） --}}
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <div class="card">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.students.update', $student) }}" class="row g-3">
        @csrf
        @method('PUT')

        <div class="col-md-6">
          <label for="name" class="form-label">氏名 <span class="text-danger">*</span></label>
          <input id="name" name="name"
                 class="form-control @error('name') is-invalid @enderror"
                 value="{{ old('name', $student->name) }}" required>
          @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
          <label for="student_number" class="form-label">学籍番号 <span class="text-danger">*</span></label>
          <input id="student_number" name="student_number"
                 class="form-control @error('student_number') is-invalid @enderror"
                 value="{{ old('student_number', $student->student_number) }}" required>
          @error('student_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
          <label for="email" class="form-label">メールアドレス <span class="text-danger">*</span></label>
          <input id="email" type="email" name="email"
                 class="form-control @error('email') is-invalid @enderror"
                 value="{{ old('email', $student->email) }}" required>
          @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
          <label for="address" class="form-label">住所</label>
          <input id="address" name="address"
                 class="form-control @error('address') is-invalid @enderror"
                 value="{{ old('address', $student->address) }}">
          @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-12 d-flex justify-content-between mt-2">
          <a href="{{ route('admin.students.show', $student) }}" class="btn btn-outline-secondary">
            キャンセル（詳細へ戻る）
          </a>
          <button class="btn btn-primary">保存する</button>
        </div>
      </form>
    </div>
  </div>

  {{-- 戻る・削除ボタン群（※編集ページなので「編集」ボタンは省略） --}}
  <div class="mt-3 d-flex justify-content-between">
    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">一覧に戻る</a>

    <form action="{{ route('admin.students.destroy', $student) }}" method="POST"
          class="d-inline"
          onsubmit="return confirm('本当に削除しますか？')">
      @csrf
      @method('DELETE')
      <button type="submit" class="btn btn-danger">削除</button>
    </form>
  </div>

</div>
</body>
</html>