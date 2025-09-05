<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Student Home</title>
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
    .actions { display:flex; gap:8px; flex-wrap:wrap; }
  </style>
</head>
<body>
  <div class="wrap">
    @php $s = isset($student) ? $student : auth('student')->user(); @endphp

    <div class="row" style="margin-bottom: 8px;">
      <h1>学生ホーム</h1>
      <form method="POST" action="{{ route('student.logout') }}">
        @csrf
        <button type="submit" class="btn outline">ログアウト</button>
      </form>
    </div>

    {{-- ステータス表示（任意） --}}
    @if (session('status'))
      <div class="ok">{{ session('status') }}</div>
    @endif

    {{-- メール未認証の案内（verified ミドルウェアを使っていない場合に表示） --}}
    @if ($s && method_exists($s, 'hasVerifiedEmail') && ! $s->hasVerifiedEmail())
      <div class="warn">
        メールアドレスが未確認です。確認メールを再送するには下のボタンを押してください。
        <form method="POST" action="{{ route('student.verification.send') }}" style="display:inline;">
          @csrf
          <button class="btn" style="margin-left:8px;">確認メールを再送する</button>
        </form>
        <a href="{{ route('student.verification.notice') }}" class="btn outline" style="margin-left:6px;">認証手順を表示</a>
      </div>
    @else
      <div class="ok">メール認証済みです。</div>
    @endif

    {{-- プロフィール & アカウント情報 --}}
    <div class="grid">
      <div class="card">
        <h2>学生情報</h2>
        @if($s)
          <div><strong>氏名：</strong>{{ $s->name }}</div>
          @isset($s->student_number)
            <div><strong>学籍番号：</strong>{{ $s->student_number }}</div>
          @endisset
          @isset($s->email)
            <div><strong>メール：</strong>{{ $s->email }}</div>
          @endisset
          <div><strong>住所：</strong>{{ $s->address ?? '未登録' }}</div>
        @else
          <div class="muted">学生情報を取得できませんでした。</div>
        @endif
      </div>

      <div class="card">
        <h2>アカウント</h2>
        <div><strong>認証状態：</strong>
          @if ($s && $s->email_verified_at) 認証済み（{{ $s->email_verified_at }}）
          @else 未認証
          @endif
        </div>
        <div class="muted" style="margin-top:8px;">
          下のボタンから学習・履修関連のページへ移動できます。
        </div>
        <div class="actions" style="margin-top:10px;">
          {{-- 既存ルートが無ければこの2つは後で差し替え・コメントアウトしてください --}}
          <a class="btn outline" href="{{ url('/student/subjects') }}">科目一覧へ</a>
          <a class="btn outline" href="{{ url('/student/enrollments') }}">履修登録科目一覧へ</a>
        </div>
      </div>
    </div>

    {{-- ダミーの表示例：成績 --}}
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
          {{-- 実データに置き換え例：@foreach($s->grades as $g) --}}
          <tr><td>作業療法概論</td><td>A</td><td>2025-07-15</td></tr>
          <tr><td>解剖学</td><td>B+</td><td>2025-07-10</td></tr>
          <tr><td>生理学</td><td>B</td><td>2025-07-03</td></tr>
          {{-- @endforeach --}}
        </tbody>
      </table>
      <div class="muted" style="margin-top:6px;">※ 上記はダミーです。成績モデルやリレーションが整ったら差し替えてください。</div>
    </div>

    {{-- ダミーの表示例：出欠 --}}
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
          {{-- 実データ例：@foreach($s->attendances()->latest()->limit(10)->get() as $a) --}}
          <tr><td>2025-07-16</td><td>出席</td><td></td></tr>
          <tr><td>2025-07-15</td><td>遅刻</td><td>電車遅延</td></tr>
          <tr><td>2025-07-14</td><td>欠席</td><td>体調不良</td></tr>
          {{-- @endforeach --}}
        </tbody>
      </table>
      <div class="muted" style="margin-top:6px;">※ こちらもダミーのテーブルです。実装後に置き換えてください。</div>
    </div>

  </div>
</body>
</html>