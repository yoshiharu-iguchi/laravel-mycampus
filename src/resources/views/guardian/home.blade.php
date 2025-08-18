<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Guardian Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans JP", sans-serif; background:#f5f5f5; }
    .wrap { max-width: 920px; margin: 6vh auto; padding: 24px; background:#fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
    h1 { margin: 0 0 16px; font-size: 22px; }
    h2 { margin: 18px 0 10px; font-size: 18px; }
    .grid { display:grid; grid-template-columns: 1fr 1fr; gap:16px; }
    .card { background:#fafafa; border:1px solid #eee; border-radius:10px; padding:16px; }
    .row { display:flex; align-items:center; justify-content:space-between; gap:12px; }
    .btn { display:inline-block; padding:10px 14px; border:0; border-radius:8px; background:#111827; color:#fff; font-size:14px; cursor:pointer; text-decoration:none; }
    .btn.outline { background:#fff; color:#111827; border:1px solid #ddd; }
    .muted { color:#666; font-size:13px; }
    .ok  { background:#e6fbe6; color:#065f46; padding:10px 12px; border-radius:8px; font-size:13px; margin-bottom:12px; }
    .warn{ background:#fff6e5; color:#7a5200; padding:10px 12px; border-radius:8px; font-size:13px; margin-bottom:12px; border:1px dashed #ffd48a; }
    table { width:100%; border-collapse:collapse; font-size:14px; }
    th, td { border-bottom:1px solid #eee; padding:8px 6px; text-align:left; }
    th { background:#f7f7f7; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="row" style="margin-bottom: 8px;">
      <h1>保護者ホーム</h1>
      <form method="POST" action="{{ route('guardian.logout') }}">
        @csrf
        <button type="submit" class="btn outline">ログアウト</button>
      </form>
    </div>

    {{-- ステータス表示（任意） --}}
    @if (session('status'))
      <div class="ok">{{ session('status') }}</div>
    @endif

    {{-- メール未認証の案内（verified ミドルウェアを使っていない場合に表示） --}}
    @php $g = auth('guardian')->user(); @endphp
    @if ($g && method_exists($g, 'hasVerifiedEmail') && ! $g->hasVerifiedEmail())
      <div class="warn">
        メールアドレスが未確認です。確認メールを再送するには下のボタンを押してください。
        <form method="POST" action="{{ route('verification.send') }}" style="display:inline;">
          @csrf
          <button class="btn" style="margin-left:8px;">確認メールを再送する</button>
        </form>
      </div>
    @endif

    {{-- プロフィール & 学生情報 --}}
    <div class="grid">
      <div class="card">
        <h2>保護者情報</h2>
        <div><strong>氏名：</strong>{{ $guardian->name ?? ($g->name ?? '') }}</div>
        <div><strong>メール：</strong>{{ $guardian->email ?? ($g->email ?? '') }}</div>
        <div class="muted" style="margin-top:8px;">このアカウントは学生1名と紐付いています。</div>
      </div>

      <div class="card">
        <h2>学生情報</h2>
        @if(isset($student))
          <div><strong>氏名：</strong>{{ $student->name }}</div>
          @isset($student->student_number)
            <div><strong>学籍番号：</strong>{{ $student->student_number }}</div>
          @endisset
          @isset($student->email)
            <div><strong>メール：</strong>{{ $student->email }}</div>
          @endisset
        @else
          <div class="muted">学生情報を取得できませんでした。</div>
        @endif
      </div>
    </div>

    {{-- 下はダミーの表示例。成績や出欠ビューがあれば差し替えてください --}}
    <div class="card" style="margin-top:16px;">
      <h2>最近の成績（例）</h2>
      <table>
        <thead>
          <tr>
            <th>科目</th>
            <th>評価</th>
            <th>更新日</th>
          </tr>
        </thead>
        <tbody>
          {{-- 実データに置き換え例：@foreach($student->grades as $grade) --}}
          <tr><td>国語</td><td>B</td><td>2025-04-10</td></tr>
          <tr><td>数学</td><td>A</td><td>2025-04-08</td></tr>
          <tr><td>英語</td><td>B+</td><td>2025-04-05</td></tr>
          {{-- @endforeach --}}
        </tbody>
      </table>
      <div class="muted" style="margin-top:6px;">※ 上記はダミーです。実データに差し替えてご利用ください。</div>
    </div>

    <div class="card" style="margin-top:16px;">
      <h2>出欠状況（例）</h2>
      <table>
        <thead>
          <tr>
            <th>日付</th>
            <th>区分</th>
            <th>備考</th>
          </tr>
        </thead>
        <tbody>
          {{-- 実データ例：@foreach($student->attendances as $a) --}}
          <tr><td>2025-04-10</td><td>出席</td><td></td></tr>
          <tr><td>2025-04-09</td><td>遅刻</td><td>交通遅延</td></tr>
          <tr><td>2025-04-08</td><td>欠席</td><td>体調不良</td></tr>
          {{-- @endforeach --}}
        </tbody>
      </table>
      <div class="muted" style="margin-top:6px;">※ こちらもダミーのテーブルです。</div>
    </div>
  </div>
</body>
</html>



