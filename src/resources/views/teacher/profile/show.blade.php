@extends('layouts.teacher')
@section('page-title','プロフィール')

@section('teacher-content')
  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="card-title mb-3">プロフィール</h5>
      <dl class="row mb-0">
        <dt class="col-sm-3">氏名</dt>
        <dd class="col-sm-9">{{ $teacher->name ?? '-' }}</dd>
        <dt class="col-sm-3">メール</dt>
        <dd class="col-sm-9">{{ $teacher->email ?? '-' }}</dd>
      </dl>
    </div>
  </div>
@endsection