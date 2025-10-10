@extends('layouts.guardian')
@section('title','プロフィール | 保護者')
@section('content')
<div class="container py-4">
  <h1 class="h4 mb-3">プロフィール</h1>

  <div class="row g-3">
    <div class="col-md-6">
      <div class="card"><div class="card-body">
        <h2 class="h6">保護者情報</h2>
        <dl class="row mb-0">
          <dt class="col-sm-4">氏名</dt><dd class="col-sm-8">{{ $guardian->name ?? '-' }}</dd>
          <dt class="col-sm-4">メール</dt><dd class="col-sm-8">{{ $guardian->email ?? '-' }}</dd>
          <dt class="col-sm-4">関係</dt><dd class="col-sm-8">{{ $guardian->relationship ?? '-' }}</dd>
        </dl>
      </div></div>
    </div>
    <div class="col-md-6">
      <div class="card"><div class="card-body">
        <h2 class="h6">学生情報</h2>
        @if($student)
          <dl class="row mb-0">
            <dt class="col-sm-4">氏名</dt><dd class="col-sm-8">{{ $student->name ?? '-' }}</dd>
            <dt class="col-sm-4">学籍番号</dt><dd class="col-sm-8" text-break>{{ $student->student_number ?? '-' }}</dd>
            <dt class="col-sm-4">住所</dt><dd class="col-sm-8" text-breakd>{{ $student->address ?? '-' }}</dd>
          </dl>
        @else
          <div class="text-muted">学生情報が紐付いていません。</div>
        @endif
      </div></div>
    </div>
  </div>

  <div class="mt-3">
    <a class="btn btn-secondary" href="{{ route('guardian.home') }}">保護者ホームへ戻る</a>
  </div>
</div>
@endsection