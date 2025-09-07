{{-- resources/views/teacher/enrollments/index.blade.php --}}
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>履修一覧（教員）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Bootstrap（必要ならレイアウトへ移動可） --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <h1 class="h4 mb-4">履修一覧（教員）</h1>

  {{-- フラッシュメッセージ／エラー --}}
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

  {{-- 検索フォーム --}}
  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" action="{{ url()->current() }}" class="row g-2">
        {{-- 科目 --}}
        <div class="col-12 col-md-5">
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
        <div class="col-6 col-md-3">
          <input type="number" name="year" value="{{ old('year', request('year')) }}"
                 class="form-control" placeholder="年度(YYYY)">
        </div>
        {{-- 学期 --}}
        <div class="col-6 col-md-3">
          <select name="term" class="form-select">
            <option value="">学期</option>
            @foreach(['前期','後期','通年'] as $t)
              <option value="{{ $t }}" @selected(request('term')===$t)>{{ $t }}</option>
            @endforeach
          </select>
        </div>
        {{-- 操作 --}}
        <div class="col-12 col-md-1 d-grid">
          <button class="btn btn-primary">検索</button>
        </div>
      </form>

      {{-- 選択中の条件（チップ表示） --}}
      <div class="mt-3 small text-muted">
        @php
          $chips = [];
          if(request('subject_id')){
            $chosen = $subjects->firstWhere('id', (int)request('subject_id'));
            $chips[] = '科目: '.($chosen->name_ja ?? $chosen->name_en ?? ('ID '.$chosen->id));
          }
          if(request('year')){ $chips[] = '年度: '.e(request('year')); }
          if(request('term')){ $chips[] = '学期: '.e(request('term')); }
        @endphp

        @if(count($chips))
          @foreach($chips as $c)
            <span class="badge text-bg-secondary me-1">{{ $c }}</span>
          @endforeach
          <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary ms-1">リセット</a>
        @else
          条件未指定
        @endif
      </div>
    </div>
  </div>

  {{-- 件数サマリー --}}
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div class="small text-muted">
      全 {{ number_format($total ?? $enrollments->total()) }} 件
      @if($enrollments->count())
        ／ 表示 {{ number_format($enrollments->firstItem()) }}–{{ number_format($enrollments->lastItem()) }} 件
      @endif
    </div>
    <div>
      {{-- 必要なら他ページへの導線をここに（例：出席簿やCSV出力など） --}}
      {{-- <a href="{{ route('teacher.attendances.index') }}" class="btn btn-sm btn-outline-primary">出席簿へ</a> --}}
    </div>
  </div>

  {{-- 結果テーブル --}}
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>学生</th>
            <th>学籍番号</th>
            <th>科目</th>
            <th>年度</th>
            <th>学期</th>
            <th>状態</th>
            <th style="width: 160px;"></th>
          </tr>
        </thead>
        <tbody>
        @forelse($enrollments as $e)
          @php
            // 状態ラベル（必要に応じて調整）
            $raw = $e->status ?? null;
            $statusLabel = match($raw){
              'active','registered' => '履修中',
              'completed'           => '修了',
              'dropped','canceled'  => '取消',
              default               => ($raw ?? '—'),
            };
            $statusClass = match($statusLabel){
              '履修中' => 'text-bg-success',
              '修了'   => 'text-bg-primary',
              '取消'   => 'text-bg-secondary',
              default  => 'text-bg-light text-dark',
            };
          @endphp
          <tr>
            <td>{{ $e->student->name }}</td>
            <td>{{ $e->student->student_number }}</td>
            <td>{{ $e->subject->name_ja ?? $e->subject->name_en ?? '不明' }}</td>
            <td>{{ $e->year ?? '—' }}</td>
            <td>{{ $e->term ?? '—' }}</td>
            <td>
              <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
            </td>
            <td class="text-end">
              {{-- 詳細ページなど必要に応じてリンクを配置 --}}
              {{-- 例：学生詳細・科目詳細・履修詳細 --}}
              {{-- <a href="{{ route('teacher.students.show',$e->student) }}" class="btn btn-sm btn-outline-secondary">学生</a> --}}
              {{-- <a href="{{ route('teacher.subjects.show',$e->subject) }}" class="btn btn-sm btn-outline-secondary">科目</a> --}}
              {{-- <a href="{{ route('teacher.enrollments.show',$e) }}" class="btn btn-sm btn-outline-primary">詳細</a> --}}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center text-muted py-5">
              データがありません。
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ページネーション（検索クエリを引き継ぐ） --}}
  <div class="mt-3">
    {{ $enrollments
        ->appends([
          'subject_id' => request('subject_id'),
          'year'       => request('year'),
          'term'       => request('term'),
        ])->links() }}
  </div>

</div>
</body>
</html>
