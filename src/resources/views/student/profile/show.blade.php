@extends('layouts.student')
@section('title', 'プロフィール')

@section('student-content')
  <h1 class="h5 mb-3">プロフィール</h1>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      @if($s)
        <dl class="row mb-0">
          <dt class="col-sm-3">氏名</dt>
          <dd class="col-sm-9">{{ $s->name }}</dd>

          @isset($s->student_number)
          <dt class="col-sm-3">学籍番号</dt>
          <dd class="col-sm-9">{{ $s->student_number }}</dd>
          @endisset

          @isset($s->email)
          <dt class="col-sm-3">メール</dt>
          <dd class="col-sm-9">{{ $s->email }}</dd>
          @endisset

          <dt class="col-sm-3">住所</dt>
          <dd class="col-sm-9">{{ $s->address ?? '未登録' }}</dd>

          <dt class="col-sm-3">認証状態</dt>
          <dd class="col-sm-9">
            @if($s->email_verified_at)
              認証済み（{{ $s->email_verified_at }}）
            @else
              未認証
              <form method="POST" action="{{ route('student.verification.send') }}" class="d-inline ms-2">
                @csrf
                <button class="btn btn-sm btn-dark">確認メールを再送</button>
              </form>
            @endif
          </dd>
        </dl>
      @else
        <p class="text-muted mb-0">学生情報を取得できませんでした。</p>
      @endif
    </div>
  </div>
@endsection