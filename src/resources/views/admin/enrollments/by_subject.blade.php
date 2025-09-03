{{-- 科目別 履修登録一覧 --}}

<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>科目別 履修登録一覧（管理）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Bootstrap --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">科目別 履修登録一覧</h1>
    <div>
      <a href="{{ route('admin.subjects.show', $subject->id) }}" class="btn btn-outline-secondary btn-sm">科目詳細</a>
      <a href="{{ route('admin.enrollments.index') }}" class="btn btn-outline-primary btn-sm">履修一覧へ戻る</a>
    </div>
  </div>

  {{-- 科目情報 --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="fw-bold">科目名</div>
      <div class="mb-2">{{ $subject->name_ja ?? $subject->name_en ?? '不明' }}</div>
    </div>
  </div>

  {{-- 件数サマリー --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div class="small text-muted">
      全 {{ number_format($enrollments->total()) }} 件
      @if($enrollments->count())
        ／ 表示 {{ number_format($enrollments->firstItem()) }}–{{ number_format($enrollments->lastItem()) }} 件
      @endif
    </div>
  </div>

  {{-- テーブル --}}
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 90px;">ID</th>
            <th>学生</th>
            <th>学籍番号</th>
            <th>年度</th>
            <th>学期</th>
            <th>状態</th>
            <th style="width: 150px;"></th>
          </tr>
        </thead>
        <tbody>
        @forelse($enrollments as $e)
          <tr>
            <td>{{ $e->id }}</td>
            <td>
              <a href="{{ route('admin.enrollments.byStudent', $e->student_id) }}" class="link-primary text-decoration-underline">
                {{ $e->student->name }}
              </a>
            </td>
            <td>{{ $e->student->student_number }}</td>
            <td>{{ $e->year }}</td>
            <td>{{ $e->term }}</td>
            <td>
              @php $status = (string)$e->status; @endphp
              <span class="badge
                @if(in_array($status, ['確定','approved','enrolled'])) text-bg-success
                @elseif(in_array($status, ['取消','canceled','rejected'])) text-bg-danger
                @elseif(in_array($status, ['保留','pending'])) text-bg-warning
                @else text-bg-secondary @endif">
                {{ $e->status_label }}
              </span>
            </td>
            <td class="text-end">
              <a href="{{ route('admin.students.show', $e->student_id) }}" class="btn btn-sm btn-outline-primary">学生詳細</a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center text-muted py-5">該当する履修登録は見つかりませんでした。</td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ページネーション --}}
  <div class="mt-3">
    {{ $enrollments->appends(request()->query())->links() }}
  </div>

</div>
</body>
</html>