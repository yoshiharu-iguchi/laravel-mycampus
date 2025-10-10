<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>@yield('title','MyCampus')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
  <div class="container">
    <a class="navbar-brand" href="{{ url('/') }}">MyCampus</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#gNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="gNav">
      <ul class="navbar-nav me-auto">
        @auth('guardian')
          <li class="nav-item"><a class="nav-link" href="{{ route('guardian.home') }}">ホーム</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('guardian.profile.show') }}">プロフィール</a></li>
        @endauth
      </ul>
      <div class="d-flex">
        @auth('guardian')
          <form method="POST" action="{{ route('guardian.logout') }}">
            @csrf
            <button class="btn btn-sm btn-outline-secondary">ログアウト</button>
          </form>
        @else
          <a class="btn btn-sm btn-primary" href="{{ route('guardian.login') }}">ログイン</a>
        @endauth
      </div>
    </div>
  </div>
</nav>

<main class="container my-4">
  @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>