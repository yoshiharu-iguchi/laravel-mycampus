@extends('layouts.admin')
@section('title','実習施設の新規登録')

@section('content')
  @includeFirst(['layouts.partials.errors','partials.errors'])

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.facilities.store') }}">
        @csrf
        <div class="mb-3">
          <label class="form-label">施設名 <span class="text-danger">*</span></label>
          <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">住所</label>
          <input type="text" name="address" value="{{ old('address') }}" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">最寄駅</label>
          <input type="text" name="nearest_station" value="{{ old('nearest_station') }}" class="form-control">
        </div>

        <div class="d-flex gap-2">
          <button class="btn btn-primary">登録</button>
          <a href="{{ route('admin.facilities.index') }}" class="btn btn-outline-secondary">キャンセル</a>
        </div>
      </form>
    </div>
  </div>
@endsection