{{-- resources/views/guardian/home.blade.php --}}
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>保護者ホーム | MyCampus</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- Bootstrap 5（CDN） --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  {{-- Bootstrap Icons（CDN） --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body { background:#f5f6f8; }
    .navbar-brand i { margin-right:.35rem; }
    .card { border:0; border-radius: 12px; box-shadow: 0 4px 18px rgba(0,0,0,.06); }
    .muted { color:#6c757d; font-size:.9rem; }
    .chip { display:inline-flex; align-items:center; gap:.35rem; font-size:.85rem; padding:.25rem .5rem; background:#f1f3f5; border-radius:999px; }
    .table thead th { font-weight:600; color:#6c757d; }
    .table td, .table th { vertical-align: middle; }
    .kpi { font-weight:700; font-size: 1.25rem; }
    .icon-btn { display:inline-flex; align-items:center; gap:.5rem; text-decoration:none; }
    .icon-btn i { font-size:1.1rem; }
  </style>
</head>
<body>

@php
  /** @var \App\Models\Guardian|null $guardian */
  /** @var \App\Models\Student|null  $student  */
  $guardian = $guardian ?? auth('guardian')->user();
  $g = $guardian; // 既存コードとの互換
  $student  = $student ?? $guardian?->student;
  $pendingCount = $pendingCount ?? null; // AppServiceProviderのcomposerで来る値（無ければnull）
@endphp

<nav class="navbar navbar-expand-lg bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="{{ url('/guardian/home') }}">
      <i class="bi bi-shield-heart"></i> <span>保護者ポータル</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div id="mainNav" class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link active" href="{{ url('/guardian/home') }}"><i class="bi bi-house-door"></i> ホーム</a>
        </li>

        {{-- ルートはあなたの環境に合わせて差し替え --}}
        <li class="nav-item">
          <a class="nav-link" href="{{ url('/guardian/student') }}"><i class="bi bi-person-badge"></i> 学生情報</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="{{ url('/guardian/attendance') }}"><i class="bi bi-clipboard-check"></i> 出欠</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="{{ url('/guardian/transport-requests') }}">
            <i class="bi bi-train-front"></i> 交通費申請
            @if(is_numeric($pendingCount) && $pendingCount > 0)
              <span class="badge text-bg-danger ms-1">{{ $pendingCount }}</span>
            @endif
          </a>
        </li>
      </ul>

      <div class="d-flex align-items-center gap-3">
        @if($guardian)
          <span class="chip"><i class="bi bi-person-heart"></i> {{ $guardian->name ?? '保護者' }}</span>
        @endif

        {{-- ログアウト（ルート名は環境ごとに異なるためURLでPOST） --}}
        <form id="logout-form" action="{{ url('/guardian/logout') }}" method="POST" class="d-none">
          @csrf
        </form>
        <a href="#" class="btn btn-outline-secondary btn-sm icon-btn" onclick="document.getElementById('logout-form').submit();return false;">
          <i class="bi bi-box-arrow-right"></i> ログアウト
        </a>
      </div>
    </div>
  </div>
</nav>

<main class="container my-4">

  {{-- フラッシュメッセージ --}}
  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  {{-- ヘッダー --}}
  <div class="row g-3 align-items-stretch">
    <div class="col-12 col-lg-8">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h1 class="h5 mb-1">ようこそ、{{ $guardian->name ?? '保護者' }} 様</h1>
              <div class="text-muted">お子さまの学習・出欠・申請状況を確認できます。</div>
            </div>
            <div class="text-end">
              @if($student)
                <div class="chip"><i class="bi bi-mortarboard"></i> 学生：{{ $student->name }}</div>
                @if(!empty($student->student_number))
                  <div class="mt-1"><span class="badge text-bg-light">学籍番号：{{ $student->student_number }}</span></div>
                @endif
              @else
                <span class="badge text-bg-warning">学生情報が紐づいていません</span>
              @endif
            </div>
          </div>

          <hr>

          <div class="row g-3">
            <div class="col-6 col-md-4">
              <div class="p-3 bg-light rounded-3 h-100">
                <div class="muted mb-1"><i class="bi bi-calendar-check"></i> 直近出席</div>
                <div class="kpi">—</div>
                <div class="muted">※ 実データに差し替え</div>
              </div>
            </div>
            <div class="col-6 col-md-4">
              <div class="p-3 bg-light rounded-3 h-100">
                <div class="muted mb-1"><i class="bi bi-graph-up"></i> 成績平均</div>
                <div class="kpi">—</div>
                <div class="muted">※ 実データに差し替え</div>
              </div>
            </div>
            <div class="col-12 col-md-4">
              <div class="p-3 bg-light rounded-3 h-100 d-flex flex-column justify-content-between">
                <div class="muted mb-1"><i class="bi bi-train-front"></i> 交通費申請（承認待ち）</div>
                <div class="kpi">{{ is_numeric($pendingCount) ? $pendingCount : '—' }}</div>
                <div>
                  <a class="icon-btn" href="{{ url('/guardian/transport-requests') }}"><i class="bi bi-arrow-right-circle"></i> 一覧を見る</a>
                </div>
              </div>
            </div>
          </div>

        </div>{{-- /card-body --}}
      </div>
    </div>

    <div class="col-12 col-lg-4">
      <div class="card h-100">
        <div class="card-body">
          <h2 class="h6 mb-3"><i class="bi bi-bell"></i> お知らせ</h2>
          <ul class="list-unstyled mb-0">
            <li class="mb-2">
              <i class="bi bi-circle-fill me-2" style="font-size:.6rem;"></i>
              実習に関する案内が公開されました。詳細は「学生情報」からご確認ください。
            </li>
            <li class="mb-2">
              <i class="bi bi-circle-fill me-2" style="font-size:.6rem;"></i>
              今月の出欠締切は毎週金曜日 17:00 です。
            </li>
          </ul>
          <div class="muted mt-2">※ 本ブロックはダミーです。通知テーブルなどに連結してください。</div>
        </div>
      </div>
    </div>
  </div>

  {{-- 2列：成績（例）／ 出欠（例） --}}
  <div class="row g-3 mt-1">
    <div class="col-12 col-lg-6">
      <div class="card">
        <div class="card-body">
          <h2 class="h6 mb-3"><i class="bi bi-journal-check"></i> 最近の成績（例）</h2>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr><th>科目</th><th>評価</th><th>更新日</th></tr>
              </thead>
              <tbody>
                <tr><td>基礎作業療法演習</td><td>B</td><td>2025-04-10</td></tr>
                <tr><td>解剖学</td><td>A</td><td>2025-04-08</td></tr>
                <tr><td>音楽療法</td><td>B+</td><td>2025-04-05</td></tr>
              </tbody>
            </table>
          </div>
          <div class="muted">※ ダミーデータです。実データに置換してください。</div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card">
        <div class="card-body">
          <h2 class="h6 mb-3"><i class="bi bi-clipboard2-check"></i> 出欠状況（例）</h2>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr><th>日付</th><th>区分</th><th>備考</th></tr>
              </thead>
              <tbody>
                <tr><td>2025-04-10</td><td>出席</td><td></td></tr>
                <tr><td>2025-04-09</td><td>遅刻</td><td>交通遅延</td></tr>
                <tr><td>2025-04-08</td><td>欠席</td><td>体調不良</td></tr>
              </tbody>
            </table>
          </div>
          <div class="muted">※ ダミーデータです。Attendance等のテーブルへ接続してください。</div>
        </div>
      </div>
    </div>
  </div>

  {{-- 交通費申請CTA --}}
  <div class="card mt-3">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <div class="h6 mb-0"><i class="bi bi-train-front"></i> 交通費申請</div>
        <div class="muted">検索URL・メモ付きの申請内容を確認／提出できます。</div>
      </div>
      <div>
        <a class="btn btn-dark" href="{{ url('/guardian/transport-requests') }}">
          <i class="bi bi-arrow-right-circle"></i> 一覧・新規申請へ
        </a>
      </div>
    </div>
  </div>

  <div class="text-center text-muted mt-4 small">© {{ date('Y') }} MyCampus</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

