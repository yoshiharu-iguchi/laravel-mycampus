<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>保護者登録</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <h1>保護者登録</h1>

  {{-- 学生情報を表示 --}}
  <p>対象の学生：{{ $student->name }}（学籍番号：{{ $student->student_number }}）</p>

  {{-- 全体エラー（必要に応じて） --}}
  @if ($errors->any())
    <div style="color:#a00; margin-bottom:12px;">
      入力内容に誤りがあります。各項目のエラーをご確認ください。
    </div>
  @endif

  {{-- 完了メッセージ --}}
  @if (session('status') === 'registered')
    <p style="color:green;">登録が完了しました。</p>
  @endif

  {{-- 登録フォーム --}}
  <form method="POST" action="{{ route('guardian.register.token.store', ['token' => $token]) }}">
    @csrf

    <div style="margin-bottom:8px;">
      <label for="name">お名前</label><br>
      <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
      @error('name')
        <div style="color:#a00; font-size:12px;">{{ $message }}</div>
      @enderror
    </div>

    <div style="margin-bottom:8px;">
      <label for="relationship">続柄</label><br>
      <select id="relationship" name="relationship" required>
        <option value="" disabled {{ old('relationship') ? '' : 'selected' }}>選択してください（父 / 母 / 祖父 / 祖母 / その他）</option>
        @foreach (['父','母','祖父','祖母','その他'] as $opt)
          <option value="{{ $opt }}" {{ old('relationship') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
        @endforeach
      </select>
      @error('relationship')
        <div style="color:#a00; font-size:12px;">{{ $message }}</div>
      @enderror
    </div>

    <div style="margin-bottom:8px;">
      <label for="email">メールアドレス</label><br>
      <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
      @error('email')
        <div style="color:#a00; font-size:12px;">{{ $message }}</div>
      @enderror
    </div>

    <div style="margin-bottom:8px;">
      <label for="password">パスワード</label><br>
      <input id="password" type="password" name="password" required autocomplete="new-password">
      @error('password')
        <div style="color:#a00; font-size:12px;">{{ $message }}</div>
      @enderror
    </div>

    <div style="margin-bottom:12px;">
      <label for="password_confirmation">パスワード（確認）</label><br>
      <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
    </div>

    <button type="submit">登録する</button>
  </form>
</body>
</html>