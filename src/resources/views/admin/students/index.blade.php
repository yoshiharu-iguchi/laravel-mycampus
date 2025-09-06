<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>学生一覧（管理）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Bootstrap（必要ならレイアウトに移してください） --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h1 class="h4 mb-4">学生一覧</h1>

  {{-- フラッシュメッセージ --}}
  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  {{-- 検索フォーム --}}
  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" action="{{ url()->current() }}" class="row g-2">
        <div class="col-sm-8 col-md-6">
          <input
            type="text"
            name="keyword"
            value="{{ old('keyword', $keyword) }}"
            class="form-control"
            placeholder="名前で検索（例：山田）">
        </div>
        <div class="col-sm-auto">
          <button type="submit" class="btn btn-primary">検索</button>
        </div>
        <div class="col-sm-auto">
          <a href="{{ url()->current() }}" class="btn btn-outline-secondary">リセット</a>
        </div>
      </form>

      <div class="mt-3 small text-muted">
        @if($keyword)
          キーワード: <span class="badge text-bg-secondary">{{ $keyword }}</span>
        @else
          キーワード未指定
        @endif
      </div>
    </div>
  </div>

  {{-- 件数サマリー --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div class="small text-muted">
      全 {{ number_format($total) }} 件
      @if($students->count())
        ／ 表示 {{ number_format($students->firstItem()) }}–{{ number_format($students->lastItem()) }} 件
      @endif
    </div>
  </div>

  {{-- 結果テーブル --}}
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <!-- <th style="width: 90px;">ID</th> -->
            <th>氏名</th>
            <th>学籍番号</th>
            <th>メールアドレス</th>
            <th style="width: 120px;">操作</th>
          </tr>
        </thead>
        <tbody>
        @forelse($students as $student)
          <tr>
            <!-- <td>{{ $student->id }}</td> -->
            <td>{{ $student->name }}</td>
            <td>{{ $student->student_number }}</td>
            <td>{{ $student->email }}</td>
            <td class="text-end">
              {{-- ルート名は resource 想定（admin.students.show）にしています --}}
              <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-outline-primary">
                詳細
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center text-muted py-5">
              該当する学生は見つかりませんでした。
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ページネーション（検索クエリを引き継ぐ） --}}
  <div class="mt-3">
    {{ $students->appends(['keyword' => $keyword])->links() }}
  </div>

</div>
</body>
</html>