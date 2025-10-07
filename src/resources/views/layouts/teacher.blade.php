<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>@yield('title','Teacher')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- Bootstrap 5 CDN --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .container-narrow { max-width: 960px; }
    .table thead th { position: sticky; top: 0; background: #fff; z-index: 1; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="{{ route('teacher.dashboard') }}">Teacher Console</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div id="nav" class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="{{ route('teacher.subjects.index') }}">担当科目</a></li>
      </ul>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn btn-outline-secondary btn-sm">ログアウト</button>
      </form>
    </div>
  </div>
</nav>

<main class="container container-narrow py-4">
  @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>