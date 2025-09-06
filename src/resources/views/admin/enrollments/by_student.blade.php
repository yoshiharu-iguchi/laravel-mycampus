{{-- 学生別 履修登録一覧 --}}

<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>学生別 履修登録一覧（管理）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Bootstrap --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">学生別 履修登録一覧</h1>
    <div>
      <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-outline-secondary btn-sm">学生詳細</a>
      <a href="{{ route('admin.enrollments.index') }}" class="btn btn-outline-primary btn-sm">履修一覧へ戻る</a>
    </div>
  </div>

  {{-- 学生情報 --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <div class="fw-bold">学生名</div>
          <div class="mb-2">{{ $student->name }}</div>
        </div>
        <div class="col-md-6">
          <div class="fw-bold">学籍番号</div>
          <div class="mb-2">{{ $student->student_number }}</div>
        </div>
      </div>
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

    @php
      // 学期（DB: 1 前期, 2 後期, 3 通年）
      $TERM = [1 => '前期', 2 => '後期', 3 => '通年'];

      // 登録状況（Draft=0, Registered=1, Approved=2, Pending=3, Canceled=4）
      $STATUS_LABEL = [
        0 => '下書き',
        1 => '履修中',
        2 => '確定',
        3 => '保留',
        4 => '取消',
      ];
      $STATUS_BADGE = [
        0 => 'text-bg-secondary',
        1 => 'text-bg-primary',
        2 => 'text-bg-success',
        3 => 'text-bg-warning',
        4 => 'text-bg-danger',
      ];
    @endphp

    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          {{-- ★ ID列は削除（全5列） --}}
          <th>科目</th>
          <th>年度</th>
          <th>学期</th>
          <th>状態</th>
          <th style="width: 150px;">操作</th>
        </tr>
      </thead>
      <tbody>
      @forelse($enrollments as $e)
        @php
          // Enumでも数値でも安全に取り出す
          $tVal = (is_object($e->term)   && property_exists($e->term, 'value'))   ? (int)$e->term->value   : (int)$e->term;
          $sVal = (is_object($e->status) && property_exists($e->status, 'value')) ? (int)$e->status->value : (int)$e->status;

          $termLabel   = $TERM[$tVal] ?? '不明';
          $statusLabel = $STATUS_LABEL[$sVal] ?? '不明';
          $badgeClass  = $STATUS_BADGE[$sVal] ?? 'text-bg-secondary';
        @endphp

        <tr>
          {{-- ★ IDセルは出力しない --}}
          <td>
            <a href="{{ route('admin.enrollments.bySubject', $e->subject_id) }}" class="link-primary text-decoration-underline">
              {{ $e->subject->name_ja ?? $e->subject->name_en ?? '不明' }}
            </a>
          </td>
          <td>{{ $e->year }}</td>
          <td>{{ $termLabel }}</td>
          <td><span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span></td>
          <td class="text-end">
            <a href="{{ route('admin.subjects.show', $e->subject_id) }}" class="btn btn-sm btn-outline-secondary">科目詳細</a>
          </td>
        </tr>
      @empty
        <tr>
          {{-- ★ 列が5つなので colspan も 5 に --}}
          <td colspan="5" class="text-center text-muted py-5">該当する履修登録は見つかりませんでした。</td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
