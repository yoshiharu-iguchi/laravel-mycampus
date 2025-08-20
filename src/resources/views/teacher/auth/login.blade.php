<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Teacher Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    /* 最小の見た目だけ。要らなければ全部消してOK */
    body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans JP", sans-serif; background:#f5f5f5; }
    .wrap { max-width: 420px; margin: 8vh auto; padding: 24px; background:#fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
    h1 { margin: 0 0 16px; font-size: 20px; }
    label { display:block; font-size: 14px; margin: 12px 0 6px; }
    input[type="email"], input[type="password"] {
      width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:8px; font-size:14px;
    }
    .row { display:flex; align-items:center; justify-content:space-between; margin-top:10px; }
    button { width:100%; margin-top:16px; padding:10px 12px; border:0; border-radius:8px; background:#111827; color:#fff; font-size:14px; cursor:pointer; }
    .err { background:#fde8e8; color:#7f1d1d; padding:10px 12px; border-radius:8px; font-size:13px; margin-bottom:12px; }
    small.muted { color:#666; font-size:12px; }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>教員ログイン</h1>

    @if ($errors->any())
      <div class="err">
        @foreach ($errors->all() as $error)
          <div>・{{ $error }}</div>
        @endforeach
      </div>
    @endif

    <form method="POST" action="{{ route('teacher.login.store') }}">
      @csrf

      <label for="email">メールアドレス</label>
      <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">

      <label for="password">パスワード</label>
      <input id="password" type="password" name="password" required autocomplete="current-password">

      <div class="row">
        <label style="display:flex;gap:8px;align-items:center;margin:0;">
          <input type="checkbox" name="remember" value="1"> <span>ログイン状態を保持する</span>
        </label>
        {{-- パスワード再設定を教員で使わない設計ならリンクは出さない --}}
      </div>

      <button type="submit">ログイン</button>
      <div style="margin-top:10px;"><small class="muted">※ 教員専用</small></div>
    </form>
  </div>
</body>
</html>
