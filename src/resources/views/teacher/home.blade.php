<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Teacher Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans JP", sans-serif; background:#f5f5f5; }
    .wrap { max-width: 640px; margin: 8vh auto; padding: 24px; background:#fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
    h1 { margin: 0 0 16px; font-size: 22px; }
    p { margin: 12px 0; font-size: 14px; }
    .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; }
    form { display:inline; }
    button { padding:6px 12px; border:0; border-radius:6px; background:#111827; color:#fff; font-size:13px; cursor:pointer; }
    small.muted { color:#666; font-size:12px; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="topbar">
      <h1>教員ホーム</h1>
      <form method="POST" action="{{ route('teacher.logout') }}">
        @csrf
        <button type="submit">ログアウト</button>
      </form>
    </div>

    <p>ようこそ、{{ Auth::guard('teacher')->user()->name }} さん。</p>
    <p>ここは教員専用のホーム画面です。</p>

    <hr>
    <small class="muted">※ このページは教員アカウントでログインした方のみが閲覧できます。</small>
  </div>
</body>
</html>
