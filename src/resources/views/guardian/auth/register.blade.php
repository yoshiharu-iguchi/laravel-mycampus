<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Guardian Register</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans JP", sans-serif; background:#f5f5f5; }
    .wrap { max-width: 420px; margin: 8vh auto; padding: 24px; background:#fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
    h1 { margin: 0 0 16px; font-size: 20px; }
    label { display:block; font-size: 14px; margin: 12px 0 6px; }
    input[type="text"], input[type="email"], input[type="password"] {
      width:100%; padding:10px 12px; border:1px solid #ddd; border-radius:8px; font-size:14px;
    }
    .row { display:flex; align-items:center; justify-content:space-between; margin-top:10px; }
    button { width:100%; margin-top:16px; padding:10px 12px; border:0; border-radius:8px; background:#111827; color:#fff; font-size:14px; cursor:pointer; }
    .err { background:#fde8e8; color:#7f1d1d; padding:10px 12px; border-radius:8px; font-size:13px; margin-bottom:12px; }
    small.muted { color:#666; font-size:12px; }
    a { color:#111827; font-size:12px; text-decoration:underline; }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>保護者 新規登録</h1>

    @if ($errors->any())
      <div class="err">
        @foreach ($errors->all() as $error)
          <div>・{{ $error }}</div>
        @endforeach
      </div>
    @endif

    <form method="POST" action="{{ route('guardian.register.store') }}">
      @csrf

      <label for="name">氏名</label>
      <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name">

      {{-- 学籍番号は保存しないが、紐付けのため入力してもらう --}}
      <label for="student_number">学籍番号（学生のもの）</label>
      <input id="student_number" type="text" name="student_number" value="{{ old('student_number') }}" required>

      <label for="email">メールアドレス</label>
      <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">

      <label for="password">パスワード</label>
      <input id="password" type="password" name="password" required autocomplete="new-password">

      <label for="password_confirmation">パスワード（確認）</label>
      <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">

      <button type="submit">登録する</button>
      <div class="row" style="margin-top:12px;">
        <small class="muted">既にアカウントをお持ちですか？</small>
        <a href="{{ route('guardian.login') }}">保護者ログインへ</a>
      </div>
    </form>
  </div>
</body>
</html>