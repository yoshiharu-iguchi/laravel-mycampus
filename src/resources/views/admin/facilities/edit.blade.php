@extends('layouts.admin')
@section('title','実習施設の編集')

@section('content')
  <h1 class="h5 mb-3">実習施設の編集</h1>
  <form method="POST" action="{{ route('admin.facilities.update',$facility) }}" class="card p-3">
    @csrf @method('PUT')
    @include('admin.facilities._form', ['facility' => $facility])
    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary">更新する</button>
      <a href="{{ route('admin.facilities.index') }}" class="btn btn-outline-secondary">戻る</a>
    </div>
  </form>
@endsection