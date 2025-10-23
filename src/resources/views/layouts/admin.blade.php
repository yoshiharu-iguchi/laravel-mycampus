<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>@yield('title', '管理画面')｜MyCampus Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Bootstrap 5 --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  {{-- Bootstrap Icons（任意）--}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .app-navbar .nav-link.active { font-weight: 600; }
    .page-header { display:flex; align-items:center; justify-content:space-between; gap:.5rem; }
    .page-header h1 { font-size:1.1rem; margin:0; }
  </style>
  @stack('head')
</head>
<body class="bg-light">
  <x-topnav role="admin" :items="[]" skin="dark" />

<!-- <nav class="navbar navbar-expand-lg navbar-dark bg-dark app-navbar">
  <div class="container-fluid">
    {{-- ★ロゴ（ダッシュボードに戻る） --}}
    <a class="navbar-brand" href="{{ route('admin.dashboard') }}">MyCampus Admin</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="adminNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.home') ? 'active' : '' }}"
             href="{{ route('admin.dashboard') }}">
            <i class="bi bi-speedometer2 me-1"></i>Dashboard
          </a>
        </li>
    


    <li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.tr.*') ? 'active' : '' }}"
     href="{{ route('admin.tr.index', ['status' => 'pending']) }}">
    <i class="bi bi-ticket-detailed me-1"></i>Route of Application
    {{-- ★ null は非表示、0 以上は表示 --}}
    @if(isset($pendingCount) && $pendingCount !== null)
      <span class="badge bg-warning text-dark ms-1">{{ $pendingCount }}</span>
    @endif
    </a>
    </li>

    <li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}"
     href="{{ route('admin.students.index') }}">
    <i class="bi bi-people me-1"></i>Students
    </a>
    </li>
    <li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}"
     href="{{ route('admin.subjects.index') }}">
    <i class="bi bi-journal-text me-1"></i>Subjects
    </a>
    </li>
    <li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}"
     href="{{ route('admin.teachers.index') }}">
    <i class="bi bi-person-badge me-1"></i>Teachers
    </a>
    </li>

        {{-- 将来：学生/教員/科目 メニューもここに追加 --}}
        {{-- <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}" href="{{ route('admin.students.index') }}"><i class="bi bi-people me-1"></i>学生</a></li> --}}
      </ul>

      <div class="d-flex align-items-center gap-2">
        <span class="text-light small">admin: {{ auth('admin')->user()->name ?? 'Administrator' }}</span>
        <form method="POST" action="{{ route('logout') }}">@csrf
          <button class="btn btn-outline-light btn-sm">ログアウト</button>
        </form>
      </div>
    </div>
  </div>
</nav> -->

<main class="container py-3">
  <div class="page-header mb-3">
    <h1>@yield('title','管理画面')</h1>
    @yield('actions')
  </div>


  @yield('content')
</main>

<footer class="text-center text-muted small py-3">
  &copy; {{ date('Y') }} MyCampus
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>