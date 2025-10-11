@extends('layouts.student')
@section('title','出席一覧 | MyCampus')
@section('content')
  <div class="card mc-card"><div class="card-body">
    <h2 class="h5 mb-3">出席一覧</h2>
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light"><tr><th>日付</th><th>科目</th><th>担当</th><th>状態</th></tr></thead>
        <tbody>
          @forelse($attendances as $a)
            <tr><td>—</td><td>—</td><td>—</td><td>—</td></tr>
          @empty
            <tr><td colspan="4" class="text-muted">データがありません</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div></div>
@endsection