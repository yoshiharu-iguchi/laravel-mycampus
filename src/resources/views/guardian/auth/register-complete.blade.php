<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>保護者登録 完了</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <h1>保護者登録が完了しました</h1>
  <p>登録が正常に完了しました。引き続きシステムをご利用ください。</p>

  <p style="margin-top:16px;">
    @if (Route::has('guardian.login'))
      <a href="{{ route('guardian.login') }}">保護者ログインへ</a>
    @else
      <a href="{{ url('/guardian/login') }}">保護者ログインへ</a>
    @endif
  </p>
</body>
</html>
