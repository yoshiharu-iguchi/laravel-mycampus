<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>履修登録一覧（学生）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Bootstrap --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h1 class="h4 mb-4">履修登録済み科目一覧</h1>

  {{-- フラッシュメッセージ --}}
  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  {{-- 件数サマリー --}}
  <div class="small text-muted mb-2">
    全 {{ number_format($enrollments->total()) }} 件
    @if($enrollments->count())
      ／ 表示 {{ number_format($enrollments->firstItem()) }}–{{ number_format($enrollments->lastItem()) }} 件
    @endif
  </div>

  {{-- 結果テーブル --}}
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>科目コード</th>
            <th>科目名</th>
            <th>年度</th>
            <th>学期</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
        @forelse($enrollments as $enrollment)
          <tr>
            <td>{{ $enrollment->id }}</td>
            <td>{{ $enrollment->subject->subject_code }}</td>
            <td>{{ $enrollment->subject->name_ja ?? $enrollment->subject->name_en ?? '名称未設定' }}</td>
            <td>{{ $enrollment->year ?? '—' }}</td>
            <td>{{ $enrollment->term ?? '—' }}</td>
            <td class="text-end">
              <form method="POST" action="{{ route('student.enrollments.destroy', $enrollment) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger"
                  onclick="return confirm('本当に取り消しますか？')">
                  取消
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-muted py-5">
              現在、履修登録している科目はありません。
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ページネーション --}}
  <div class="mt-3">
    {{ $enrollments->links() }}
  </div>

</div>
</body>
</html>