<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Student Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans JP", sans-serif; background:#f5f5f5; }
    .wrap { max-width: 720px; margin: 8vh auto; padding: 24px; background:#fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
    h1 { margin: 0 0 12px; font-size: 20px; }
    p,li,small { color:#333; font-size:14px; line-height:1.7; }
    .muted { color:#666; }
    .badge { display:inline-block; padding:.15rem .5rem; border-radius:9999px; font-size:12px; }
    .ok { background:#e6fbe6; color:#065f46; }
    .warn { background:#fff8e1; color:#7a5b00; }
    .row { display:flex; gap:12px; flex-wrap:wrap; margin-top:16px; }
    button { padding:10px 12px; border:0; border-radius:8px; background:#111827; color:#fff; font-size:14px; cursor:pointer; }
    .secondary { background:#6b7280; }
    form { display:inline; }
    .card { padding:16px; border:1px solid #eee; border-radius:12px; }
    dt { font-weight:600; }
    dd { margin:0 0 8px 0; }
  </style>
</head>
<body>
  <div class="wrap">
    @php($student = auth('student')->user())

    <h1>こんにちは、{{ $student->name }} さん</h1>

    {{-- 認証状態 --}}
    @if ($student->email_verified_at)
      <div class="badge ok">メール認証済み</div>
    @else
      <div class="badge warn">メール未認証</div>
      <p class="muted">メールの認証がまだです。メールに届いたリンクを開くか、下のボタンで認証メールを再送できます。</p>
      <div class="row">
        <form method="POST" action="{{ route('student.verification.send', [], false) }}">
          @csrf
          <button type="submit">認証メールを再送</button>
        </form>
        <a href="{{ route('student.verification.notice', [], false) }}">
          <button type="button" class="secondary">認証手順を表示</button>
        </a>
      </div>
    @endif

    {{-- プロフィール（簡易表示） --}}
    <div class="card" style="margin-top:16px;">
      <dl>
        <dt>氏名</dt>
        <dd>{{ $student->name }}</dd>
        <dt>学籍番号</dt>
        <dd>{{ $student->student_number }}</dd>
        <dt>メール</dt>
        <dd>{{ $student->email }}</dd>
        <dt>住所</dt>
        <dd>{{ $student->address ?? '未登録' }}</dd>
      </dl>
    </div>

    {{-- 操作 --}}
    <div class="row">
      <form method="POST" action="{{ route('student.logout', [], false) }}">
        @csrf
        <button type="submit" class="secondary">ログアウト</button>
      </form>
    </div>

    {{-- フラッシュメッセージ --}}
    @if (session('status'))
      <p class="muted" style="margin-top:12px;">{{ session('status') }}</p>
    @endif
  </div>
</body>
</html>