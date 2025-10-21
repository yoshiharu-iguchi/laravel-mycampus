@extends('layouts.teacher')
@section('page-title','実習施設の編集')

@section('teacher-content')
  @includeFirst(['layouts.partials.errors','partials.errors'])

  <div class="card shadow-sm">
    <div class="card-header fw-semibold">
      <i class="fa-solid fa-pen-to-square me-1"></i> 実習施設の編集
    </div>
    <form method="POST" action="{{ route('teacher.facilities.update',$facility) }}">
      @csrf @method('PUT')
      @include('teacher.facilities._form')
      <div class="card-footer d-flex gap-2">
        <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> 更新する</button>
        <a href="{{ route('teacher.facilities.index') }}" class="btn btn-outline-secondary">戻る</a>
      </div>
    </form>
  </div>
@endsection