<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>科目詳細（管理者）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Bootstrap --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h1 class="h4 mb-4">{{ $subject->name_ja }}（{{ $subject->subject_code }}）</h1>

  {{-- 科目概要 --}}
  <div class="card mb-3">
    <div class="card-body">
      <p class="mb-1">単位：{{ $subject->credits ?? '—' }}</p>
      <p class="mb-1">履修者数：{{ $subject->enrollments->count() }} 名</p>
    </div>
  </div>

  {{-- 履修学生一覧 --}}
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>学籍番号</th>
            <th>氏名</th>
            <th>Email</th>
          </tr>
        </thead>
        <tbody>
        @forelse($subject->enrollments as $enrollment)
          <tr>
            <td>{{ $enrollment->student->id }}</td>
            <td>{{ $enrollment->student->student_number }}</td>
            <td>{{ $enrollment->student->name }}</td>
            <td>{{ $enrollment->student->email }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="text-center text-muted py-5">
              履修者はいません。
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
</body>
</html>
