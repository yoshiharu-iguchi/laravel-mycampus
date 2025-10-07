<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>学生ホーム | MyCampus</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Bootstrap（CDN版） --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .card { border-radius: 12px; }
    .display-6 { font-size: 1.8rem; }
    .navbar-brand { font-weight: bold; }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="{{ route('student.home') }}">MyCampus</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a href="{{ route('student.profile.show') }}" class="nav-link">プロフィール</a></li>
        <li class="nav-item"><a href="#" class="nav-link text-danger"
            onclick="event.preventDefault();document.getElementById('logout-form').submit();">ログアウト</a></li>
      </ul>
      <form id="logout-form" method="POST" action="{{ route('logout') }}" class="d-none">@csrf</form>
    </div>
  </div>
</nav>

<div class="container py-4">
  <h1 class="h4 mb-3">学生ホーム</h1>
  <p class="text-muted">ようこそ、{{ $student->name ?? '（学生名）' }} さん。</p>

  {{-- KPIカード --}}
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card shadow-sm text-center">
        <div class="card-body">
          <div class="fw-bold text-secondary">履修科目数</div>
          <div class="display-6">{{ $kpi['subjects'] ?? 0 }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm text-center">
        <div class="card-body">
          <div class="fw-bold text-secondary">平均スコア</div>
          <div class="display-6">
            {{ is_null($kpi['avgScoreOverall']) ? '—' : $kpi['avgScoreOverall'] }}
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm text-center">
        <div class="card-body">
          <div class="fw-bold text-secondary">出席数（合計）</div>
          <div class="display-6">{{ $kpi['presentTotal'] ?? 0 }}</div>
        </div>
      </div>
    </div>
  </div>

  {{-- 科目ごとの成績・出欠テーブル --}}
  <div class="card shadow-sm">
    <div class="card-body">
      <h2 class="h6 mb-3">科目別の出席・成績状況</h2>

      @if (empty($rows))
        <p class="text-muted mb-0">データがまだ登録されていません。</p>
      @else
        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>科目コード</th>
                <th>科目名</th>
                <th class="text-end">出席</th>
                <th class="text-end">欠席</th>
                <th class="text-end">遅刻</th>
                <th class="text-end">公欠</th>
                <th class="text-end">未記録</th>
                <th class="text-end">出席率</th>
                <th class="text-end">平均点</th>
                <th class="text-end">最新点</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($rows as $r)
                <x-subject-summary-row :r="$r" />
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>
</div>

</body>
</html>