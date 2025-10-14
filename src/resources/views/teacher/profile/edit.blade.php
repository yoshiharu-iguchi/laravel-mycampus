@extends('layouts.teacher')
@section('page-title','プロフィール編集')

@section('teacher-content')
  <form method="POST" action="{{ route('teacher.profile.update') }}" class="card shadow-sm p-3">
    @csrf
    @method('patch')

    <div class="mb-3">
      <label class="form-label">氏名</label>
      <input type="text" name="name" class="form-control" value="{{ old('name', $teacher->name) }}">
      @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
      <label class="form-label">メール</label>
      <input type="email" name="email" class="form-control" value="{{ old('email', $teacher->email) }}">
      @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>

    <div class="d-flex gap-2">
      <button class="btn btn-primary">更新する</button>
      <a href="{{ route('teacher.profile.show') }}" class="btn btn-outline-secondary">戻る</a>
    </div>
  </form>
@endsection