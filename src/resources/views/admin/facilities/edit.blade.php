@extends('layouts.admin')
@section('title','実習施設の編集')

@section('content')
  @includeFirst(['layouts.partials.errors','partials.errors'])

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="{{ route('admin.facilities.update',$facility) }}">
        @csrf @method('PUT')
        <div class="mb-3">
          <label class="form-label">施設名 <span class="text-danger">*</span></label>
          <input type="text" name="name" value="{{ old('name',$facility->name) }}" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">住所</label>
          <input type="text" name="address" value="{{ old('address',$facility->address) }}" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">最寄駅</label>
          <input type="text" name="nearest_station" value="{{ old('nearest_station',$facility->nearest_station) }}" class="form-control">
        </div>

        <div class="d-flex gap-2">
          <button class="btn btn-primary">更新</button>
          <a href="{{ route('admin.facilities.index') }}" class="btn btn-outline-secondary">戻る</a>
        </div>
      </form>
    </div>
  </div>
@endsection