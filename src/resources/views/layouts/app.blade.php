<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>@yield('title', 'MyCampus')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  {{-- CDN派：そのままBootstrap --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  {{-- Vite派なら上を消して @vite(['resources/css/app.css','resources/js/app.js']) に差し替え --}}
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
    <a class="navbar-brand" href="{{ url('/') }}">MyCampus</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto">
        @auth('student')
          <li class="nav-item"><a class="nav-link" href="{{ route('student.home') }}">学生ホーム</a></li>
          @if (Route::has('student.tr.create'))
+            <li class="nav-item"><a class="nav-link" href="{{ route('student.tr.create') }}">交通費申請</a></li>
+          @endif
        @endauth
        @auth('teacher')
          <li class="nav-item"><a class="nav-link" href="{{ route('teacher.home') }}">教員ホーム</a></li>
        @endauth
        @auth('admin')
          <li class="nav-item"><a class="nav-link" href="{{ route('admin.tr.index') }}">申請一覧</a></li>
        @endauth
      </ul>

      {{-- 認証状態の簡易表示（ログイン/ログアウトは各実装に合わせて調整） --}}
      <ul class="navbar-nav">
        @php $loggedIn = auth('student')->check() || auth('teacher')->check() || auth('admin')->check(); @endphp
        @if(!$loggedIn)
          <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">ログイン</a></li>
        @else
          <li class="nav-item"><span class="navbar-text">ようこそ</span></li>
        @endif
      </ul>
    </div>
  </div>
</nav>

<main class="container py-4">
  {{-- フラッシュ/エラー共通枠 --}}
  @if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>