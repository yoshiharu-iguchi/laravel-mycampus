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
  @php
    use App\Enums\EnrollmentStatus;
    use App\Enums\Term;
  @endphp
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

    @php
      // 学期：DBには 1,2,3 が入っている想定
      $TERM = [1 => '前期', 2 => '後期', 3 => '通年'];

      // 登録状況：Draft=0, Registered=1, Approved=2, Pending=3, Canceled=4
      $STATUS_LABEL = [
        0 => '下書き',
        1 => '履修中',
        2 => '確定',
        3 => '保留',
        4 => '取消',
      ];
      // バッジ（色）クラス
      $STATUS_BADGE = [
        0 => 'text-bg-secondary', // 下書き
        1 => 'text-bg-primary',   // 履修中
        2 => 'text-bg-success',   // 確定
        3 => 'text-bg-warning',   // 保留
        4 => 'text-bg-danger',    // 取消
      ];
    @endphp

    <table class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          {{-- ★ ID列は削除しました（合計6列） --}}
          <th>学生</th>
          <th>学籍番号</th>
          <th>年度</th>
          <th>開講期間</th>
          <th>登録状況</th>
          <th style="width: 180px;">操作</th>
        </tr>
      </thead>
      <tbody>
      @forelse($enrollments as $e)
        @php
          // Enum（オブジェクト）のときは ->value、数値のときはそのまま扱う
          $tVal = is_object($e->term)   ? $e->term->value   : (int)$e->term;   // 1/2/3
          $sVal = is_object($e->status) ? $e->status->value : (int)$e->status; // 0..4

          $termLabel   = $TERM[$tVal] ?? '不明';
          $statusLabel = $STATUS_LABEL[$sVal] ?? '不明';
          $badgeClass  = $STATUS_BADGE[$sVal] ?? 'text-bg-secondary';
        @endphp

        <tr>
          {{-- ★ IDセルは削除しました --}}
          <td>
            <a href="{{ route('admin.enrollments.byStudent', $e->student_id) }}"
               class="link-primary text-decoration-underline">
              {{ $e->student->name }}
            </a>
          </td>
          <td>{{ $e->student->student_number }}</td>
          <td>{{ $e->year }}</td>

          {{-- 開講期間：必ず日本語 --}}
          <td>{{ $termLabel }}</td>

          {{-- 登録状況：色つきバッジ＋日本語 --}}
          <td>
            <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
          </td>

          {{-- 操作（行末） --}}
          <td class="text-end">
            <a href="{{ route('admin.students.show', $e->student_id) }}"
               class="btn btn-sm btn-outline-primary">学生詳細</a>
          </td>
        </tr>
      @empty
        <tr>
          {{-- ★ 列が6つになったので colspan も 6 に変更 --}}
          <td colspan="6" class="text-center text-muted py-5">
            該当する履修登録は見つかりませんでした。
          </td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
</html>
