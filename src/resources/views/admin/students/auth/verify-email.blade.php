@extends('layouts.student')
@section('page-title','メール認証のお願い')

@section('student-content')
  @if (session('status') === 'verification-link-sent')
    <div class="alert alert-success">認証メールを再送しました。受信ボックスをご確認ください。</div>
  @endif

  <div class="card shadow-sm">
    <div class="card-body">
      <h2 class="h6 mb-3">メール認証のお願い</h2>
      <p class="text-muted mb-3">
        ご登録のメールアドレス宛に<strong>認証リンク</strong>を送信しました。メール内のリンクをクリックして認証を完了してください。
      </p>

      <div class="d-flex gap-2">
        {{-- 認証メールの再送 --}}
        <form method="POST" action="{{ route('student.verification.send') }}">
          @csrf
          <button type="submit" class="btn btn-primary btn-sm">認証メールを再送する</button>
        </form>

        {{-- ログアウト（任意） --}}
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn btn-outline-secondary btn-sm">ログアウト</button>
        </form>
      </div>
    </div>
  </div>
@endsection
