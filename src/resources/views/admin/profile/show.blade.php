@extends('layouts.admin')
@section('title','プロフィール')

@section('content')
  <div class="card">
    <div class="card-body">
      <h2 class="h5 mb-3">管理者プロフィール</h2>
      <dl class="row mb-0">
        <dt class="col-sm-3">氏名</dt>
        <dd class="col-sm-9">{{ $admin->name ?? '-' }}</dd>
        <dt class="col-sm-3">メール</dt>
        <dd class="col-sm-9">{{ $admin->email ?? '-' }}</dd>
      </dl>
    </div>
  </div>
@endsection