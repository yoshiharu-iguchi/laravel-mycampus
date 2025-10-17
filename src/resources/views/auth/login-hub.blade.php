<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>共通ログイン | MyCampus</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:520px;">
  <h1 class="h4 mb-4 text-center">共通ログイン</h1>

  <ul class="nav nav-pills nav-fill mb-3" id="roleTabs">
    <li class="nav-item"><button type="button" class="nav-link active" data-role="student">学生</button></li>
    <li class="nav-item"><button type="button" class="nav-link" data-role="teacher">教員</button></li>
    <li class="nav-item"><button type="button" class="nav-link" data-role="guardian">保護者</button></li>
    <li class="nav-item"><button type="button" class="nav-link" data-role="admin">管理者</button></li>
  </ul>

  <div class="card shadow-sm">
    <div class="card-body">
      @if ($errors->any())
        <div class="alert alert-danger small">
          @foreach ($errors->all() as $e)
            <div>{{ $e }}</div>
          @endforeach
        </div>
      @endif

      <form id="unifiedLoginForm" method="POST" action="#">
        @csrf
        <input type="hidden" name="from_hub" value="1">

        <div class="mb-3">
          <label class="form-label">メールアドレス</label>
          <input type="email" name="email" class="form-control" required autofocus autocomplete="username" value="{{ old('email') }}">
        </div>

        <div class="mb-3">
          <label class="form-label">パスワード</label>
          <input type="password" name="password" class="form-control" required autocomplete="current-password">
        </div>

        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" value="1" name="remember" id="rememberCheck">
          <label class="form-check-label" for="rememberCheck">ログイン状態を保持する</label>
        </div>

        <button class="btn btn-primary w-100" type="submit">ログイン</button>
      </form>
    </div>
  </div>

  {{-- === 新規登録（学生のみリンク） === --}}
  <div class="card mt-3">
    <div class="card-header">新規登録</div>
    <div class="card-body">
      <a href="{{ route('student.register') }}" class="btn btn-outline-secondary w-100">
        学生の新規登録はこちら
      </a>
      <div class="form-text mt-2">
        保護者の方は、学生のメール認証完了後に届く招待メールのリンクから登録してください。
      </div>
    </div>
  </div>

  {{-- パスワードを忘れた方 --}}
  <div class="text-center mt-3">
    <a href="{{ route('password.request') }}" class="small">パスワードをお忘れの方はこちら</a>
  </div>
</div>

<script>
  // 既存のログインPOSTルート（route:list で確認済み）
  const endpoints = {
    student:  @json(route('student.login.store')),
    teacher:  @json(route('teacher.login.store')),
    guardian: @json(route('guardian.login.store')),
    admin:    @json(route('admin.login.store')),
  };

  const tabs = document.querySelectorAll('#roleTabs .nav-link');
  const form = document.getElementById('unifiedLoginForm');

  // デフォルトは学生
  let currentRole = 'student';
  form.action = endpoints[currentRole];

  tabs.forEach(btn => {
    btn.addEventListener('click', () => {
      tabs.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      currentRole = btn.dataset.role;
      form.action = endpoints[currentRole];
    });
  });

  // 二重送信ガード（任意）
  form.addEventListener('submit', () => {
    const btn = form.querySelector('button[type="submit"]');
    if (btn) { btn.disabled = true; btn.textContent = '送信中...'; }
  });
</script>
</body>
</html>