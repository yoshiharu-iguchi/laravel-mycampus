<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>@yield('title', 'MyCampus')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background:#f5f5f5; }
    .navbar { box-shadow: 0 2px 8px rgba(0,0,0,.06); }
    .card { border-radius: 12px; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('teacher.dashboard') }}">
      <i class="bi bi-mortarboard"></i> MyCampus 教員
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('teacher.dashboard') || request()->routeIs('teacher.home') ? 'active' : '' }}"
             href="{{ route('teacher.dashboard') }}">
            <i class="bi bi-house-door"></i> ホーム
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('teacher.enrollments.*') ? 'active' : '' }}"
             href="{{ route('teacher.enrollments.index') }}">
            <i class="bi bi-people"></i> 履修
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('teacher.attendances.*') ? 'active' : '' }}"
             href="{{ route('teacher.attendances.index') }}">
            <i class="bi bi-check2-square"></i> 出欠
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('teacher.grades.*') ? 'active' : '' }}"
             href="{{ route('teacher.grades.index') }}">
            <i class="bi bi-card-checklist"></i> 成績
          </a>
        </li>
      </ul>

      <div class="d-flex align-items-center gap-3">
        <span class="text-muted small d-none d-md-inline">
          <i class="bi bi-person-circle"></i>
          {{ Auth::guard('teacher')->user()->name ?? 'Teacher' }}
        </span>
        <form method="POST" action="{{ route('logout') }}" class="m-0">
          @csrf
          <button class="btn btn-outline-dark btn-sm" type="submit">
            <i class="bi bi-box-arrow-right"></i> ログアウト
          </button>
        </form>
      </div>
    </div>
  </div>
</nav>

<main class="container py-4">
  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif

  @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>