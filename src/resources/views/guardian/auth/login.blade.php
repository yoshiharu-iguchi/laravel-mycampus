<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Guardian Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans JP", sans-serif; background:#f5f5f5; }
    .wrap { max-width: 420px; margin: 8vh auto; padding: 24px; background:#fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
    h1 { margin: 0 0 16px; font-size: 20px; }
    label { display:block; font-size: 14px; margin: 12px 0 6px; }
    input[type="email"], input[type="password"] {
      width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:8px; font-size:14px;
    }
    .row { display:flex; align-items:center; justify-content:space-between; margin-top:10px; }
    .row a { color:#111827; font-size:12px; text-decoration:underline; }
    .muted { color:#666; font-size:12px; }
    button { width:100%; margin-top:16px; padding:10px 12px; border:0; border-radius:8px; background:#111827; color:#fff; font-size:14px; cursor:pointer; }
    .err { background:#fde8e8; color:#7f1d1d; padding:10px 12px; border-radius:8px; font-size:13px; margin-bottom:12px; }
    .ok  { background:#e6fbe6; color:#065f46; padding:10px 12px; border-radius:8px; font-size:13px; margin-bottom:12px; }
    .inline { display:flex; align-items:center; gap:8px; margin-top:8px; }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>保護者 ログイン</h1>

    {{-- ステータスメッセージ（任意） --}}
    @if (session('status'))
      <div class="ok">{{ session('status') }}</div>
    @endif

    {{-- バリデーションエラー --}}
    @if ($errors->any())
      <div class="err">
        @foreach ($errors->all() as $error)
          <div>・{{ $error }}</div>
        @endforeach
      </div>
    @endif

    {{-- 相対URLでPOST（419回避に有効） --}}
    <form method="POST" action="{{ route('guardian.login.store', [], false) }}">
      @csrf

      <label for="email">メールアドレス</label>
      <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" autofocus>

      <label for="password">パスワード</label>
      <input id="password" type="password" name="password" required autocomplete="current-password">

      <div class="inline">
        <input id="remember" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
        <label for="remember" style="margin:0;">ログイン状態を保持する</label>
      </div>

      <button type="submit">ログイン</button>

      <div class="row" style="margin-top:12px;">
        <small class="muted">アカウントをお持ちでない方は</small>
        <a href="{{ route('guardian.register', [], false) }}">新規登録へ</a>
      </div>
    </form>
  </div>
</body>
</html>