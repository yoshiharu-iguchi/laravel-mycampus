<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>出席・成績ダッシュボード（学生）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Bootstrap --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">

  {{-- 見出し --}}
  <h1 class="h4 mb-3">出席・成績ダッシュボード</h1>
  <div class="small text-muted mb-3">
    ※ログイン中のあなた（学生本人）のデータです
  </div>

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

  {{-- 出席・成績一覧テーブル --}}
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>科目コード</th>
            <th>科目名</th>
            <th class="text-center">出席率</th>
            <th class="text-center">出席</th>
            <th class="text-center">遅刻</th>
            <th class="text-center">欠席</th>
            <th class="text-center">公欠</th>
            <th class="text-center">未記録</th>
            <th class="text-center">評定</th>
            <!-- <th class="text-center">平均点</th> -->
          </tr>
        </thead>
        <tbody>
        @forelse($rows as $r)
          <tr>
            <td>{{ $r['subject_code'] }}</td>
            <td>{{ $r['subject'] }}</td>
            <td class="text-center">
              {{ is_null($r['attendanceRate']) ? '—' : ($r['attendanceRate'].'%') }}
            </td>
            <td class="text-center">{{ $r['present'] }}</td>
            <td class="text-center">{{ $r['late'] }}</td>
            <td class="text-center">{{ $r['absent'] }}</td>
            <td class="text-center">{{ $r['excused'] }}</td>
            <td class="text-center">{{ $r['unrecorded'] }}</td>
            <td class="text-center">
              {{ is_null($r['latestScore']) ? '—' : $r['latestScore'] }}
            </td>
            <!-- <td class="text-center">
              {{ is_null($r['avgScore']) ? '—' : $r['avgScore'] }}
            </td> -->
          </tr>
        @empty
          <tr>
            <td colspan="10" class="text-center text-muted py-5">
              出席・成績データがありません。
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