<!doctype html>
<html lang="ja" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title','MyCampus')</title>

  {{-- 既にViteでBootstrapを読んでいるなら下のCDNは不要です --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    /* 統一の微調整 */
    .app-navbar { border-bottom: 1px solid #e9ecef; background: #fff; }
    .app-brand   { font-weight: 700; letter-spacing: .2px; }
    .app-wrap    { max-width: 1100px; }
    .card + .card { margin-top: 1rem; }
    .table-sm th, .table-sm td { vertical-align: middle; }
    /* 枠線付き表（ご要望） */
    .table-borders { border: 1px solid #dee2e6; }
    .table-borders th, .table-borders td { border: 1px solid #dee2e6; }
    /* 出席率の色分け */
    .rate-good { color: #198754; font-weight: 600; }
    .rate-warn { color: #dc3545; font-weight: 600; }
    /* スマホで科目名折返し抑制＆テーブル横スクロール */
    .subject-name { white-space: nowrap; }
  </style>
  @stack('head')
</head>
<body>
  {{-- 共通ナビ（ロール別ナビは子レイアウトで差し替え） --}}
  <header class="app-navbar">
    <nav class="navbar navbar-expand-lg app-wrap mx-auto">
      <a class="navbar-brand app-brand" href="{{ route('dashboard') }}">MyCampus</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNav">
        {{-- ここは子レイアウトが @section('nav') で埋める --}}
        @yield('nav')

        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">ホーム</a></li>
          <li class="nav-item">
            <form action="{{ route('logout') }}" method="post" class="d-inline">
              @csrf
              <button class="btn btn-sm btn-outline-secondary ms-2">ログアウト</button>
            </form>
          </li>
        </ul>
      </div>
    </nav>
  </header>

  <main class="py-3">
    <div class="container app-wrap">
      @yield('content')
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>
</html>