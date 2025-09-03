{{-- 履修登録一覧（管理） --}}

<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>履修登録一覧（管理）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Bootstrap --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h1 class="h4 mb-4">履修登録一覧</h1>

  {{-- フラッシュメッセージ --}}
  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  {{-- 検索フォーム --}}
  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" action="{{ url()->current() }}" class="row g-2">

        {{-- 科目 --}}
        <div class="col-12 col-md-4">
          <select name="subject_id" class="form-select">
            <option value="">科目を選択</option>
            @foreach($subjects as $s)
              <option value="{{ $s->id }}" @selected((string)request('subject_id') === (string)$s->id)>
                {{ $s->name_ja ?? $s->name_en ?? '不明' }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- 年度 --}}
        <div class="col-6 col-md-2">
          <input type="number" name="year" value="{{ request('year') }}"
                 class="form-control" placeholder="年度(YYYY)" min="2000" max="2100">
        </div>

        {{-- 学期 --}}
        <div class="col-6 col-md-2">
          <select name="term" class="form-select">
            <option value="">学期</option>
            @foreach(['前期','後期','通年'] as $t)
              <option value="{{ $t }}" @selected(request('term')===$t)>{{ $t }}</option>
            @endforeach
          </select>
        </div>

        {{-- キーワード（学生名 or 学籍番号） --}}
        <div class="col-12 col-md-3">
          <input type="text" name="keyword" value="{{ old('keyword', $keyword) }}"
                 class="form-control" placeholder="学生名 or 学籍番号">
        </div>

        <div class="col-6 col-md-1 d-grid">
          <button type="submit" class="btn btn-primary">検索</button>
        </div>
        <div class="col-6 col-md-auto d-grid">
          <a href="{{ url()->current() }}" class="btn btn-outline-secondary">リセット</a>
        </div>
      </form>

      {{-- 選択中フィルタ表示 --}}
      <div class="mt-3 small text-muted">
        @php
          $selectedSubject = null;
          if (request('subject_id')) {
            $selectedSubject = $subjects->firstWhere('id', (int)request('subject_id'));
          }
        @endphp
        <span class="me-2">フィルタ:</span>
        @if(request('subject_id'))
          <span class="badge text-bg-secondary me-1">
            科目: {{ $selectedSubject->name_ja ?? $selectedSubject->name_en ?? ('ID:'.request('subject_id')) }}
          </span>
        @endif
        @if(request('year'))
          <span class="badge text-bg-secondary me-1">年度: {{ request('year') }}</span>
        @endif
        @if(request('term'))
          <span class="badge text-bg-secondary me-1">学期: {{ request('term') }}</span>
        @endif
        @if($keyword)
          <span class="badge text-bg-secondary me-1">KW: {{ $keyword }}</span>
        @endif
        @if(!request('subject_id') && !request('year') && !request('term') && !$keyword)
          <span class="text-muted">未指定</span>
        @endif
      </div>
    </div>
  </div>

  {{-- 件数サマリー --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div class="small text-muted">
      全 {{ number_format($total) }} 件
      @if($enrollments->count())
        ／ 表示 {{ number_format($enrollments->firstItem()) }}–{{ number_format($enrollments->lastItem()) }} 件
      @endif
    </div>
  </div>

  {{-- 結果テーブル --}}
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th style="width: 90px;">ID</th>
            <th>学生</th>
            <th>学籍番号</th>
            <th>科目</th>
            <th>年度</th>
            <th>学期</th>
            <th>状態</th>
            <th style="width: 180px;"></th>
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
            <td>
              <a href="{{ route('admin.enrollments.bySubject', $e->subject_id) }}" class="link-primary text-decoration-underline">
                {{ $e->subject->name_ja ?? $e->subject->name_en ?? '不明' }}
              </a>
            </td>
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
              <div class="btn-group">
                <a href="{{ route('admin.students.show', $e->student_id) }}" class="btn btn-sm btn-outline-primary">学生詳細</a>
                <a href="{{ route('admin.subjects.show', $e->subject_id) }}" class="btn btn-sm btn-outline-secondary">科目詳細</a>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center text-muted py-5">
              該当する履修登録は見つかりませんでした。
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ページネーション（検索クエリを引き継ぐ） --}}
  <div class="mt-3">
    {{ $enrollments->appends(request()->query())->links() }}
  </div>

</div>
</body>
</html>
