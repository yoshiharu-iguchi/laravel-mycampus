{{-- resources/views/admin/home.blade.php --}}
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Admin Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans JP", sans-serif; background:#f5f5f5; }
    .wrap { max-width: 880px; margin: 6vh auto; padding: 24px; background:#fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
    h1 { margin:0 0 12px; font-size: 22px; }
    .sub { color:#666; font-size: 13px; margin-bottom: 16px; }
    button { padding:10px 12px; border:0; border-radius:8px; background:#111827; color:#fff; font-size:14px; cursor:pointer; }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>管理者ホーム</h1>
    <div class="sub">ログイン中: <strong>{{ optional(auth('admin')->user())->email }}</strong></div>

    <form method="POST" action="{{ route('admin.logout') }}">
      @csrf
      <button type="submit">ログアウト</button>
    </form>
  </div>
  <a href="{{ route('admin.enrollments.index') }}" class="btn btn-sm btn-primary">履修一覧へ</a>
</body>
</html>