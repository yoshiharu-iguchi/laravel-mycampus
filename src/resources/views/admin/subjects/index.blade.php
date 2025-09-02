<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>科目一覧（管理）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Bootstrap（必要ならレイアウトに移してください） --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h1 class="h4 mb-4">科目一覧</h1>

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
            placeholder="科目名で検索（例：作業療法概論）">
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
      @if($subjects->count())
        ／ 表示 {{ number_format($subjects->firstItem()) }}–{{ number_format($subjects->lastItem()) }} 件
      @endif
    </div>
    <div>
      <a href="{{ route('admin.subjects.create') }}" class="btn btn-sm btn-success">新規登録</a>
    </div>
  </div>

  {{-- 結果テーブル --}}
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 90px;">ID</th>
            <th>科目コード</th>
            <th>科目名</th>
            <th>単位</th>
            <th>年度</th>
            <th>開講期間</th>
            <th>必修/選択</th>
            <th>定員</th>
            <th style="width: 160px;"></th>
          </tr>
        </thead>
        <tbody>
        @forelse($subjects as $subject)
          <tr>
            <td>{{ $subject->id }}</td>
            <td>{{ $subject->subject_code }}</td>
            <td>{{ $subject->name_ja }}</td>
            <td>{{ $subject->credits }}</td>
            <td>{{ $subject->year }}</td>
            <td>{{ $subject->term }}</td>
            <td>{{ $subject->category }}</td>
            <td>{{ $subject->capacity }}</td>
            <td class="text-end">
              <a href="{{ route('admin.subjects.show', $subject) }}" class="btn btn-sm btn-outline-primary">詳細</a>
              <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-sm btn-outline-secondary">編集</a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="text-center text-muted py-5">
              該当する科目は見つかりませんでした。
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ページネーション（検索クエリを引き継ぐ） --}}
  <div class="mt-3">
    {{ $subjects->appends(['keyword' => $keyword])->links() }}
  </div>

</div>
</body>
</html>