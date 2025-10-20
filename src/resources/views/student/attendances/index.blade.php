@extends('layouts.student')
@section('title','出席一覧 | MyCampus')
@section('content')
  <div class="card mc-card"><div class="card-body">
    <h2 class="h5 mb-3">出席一覧</h2>

    @if($attendances->count() === 0)
      <div class="text-muted">データがありません</div>
    @else
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-light">
            <tr><th>日付</th><th>科目</th><th>担当</th><th>状態</th></tr>
          </thead>
          <tbody>
            @foreach($attendances as $a)
              <tr>
                <td>{{ optional($a->date)->format('Y-m-d') }}</td>
                <td>{{ $a->subject->name_ja ?? $a->subject->name_en ?? '—' }}</td>
                <td>{{ optional(optional($a->subject)->teacher)->name ?? '—' }}</td>
                <td>{{ $a->status_label ?? $a->status }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="mt-2">{{ $attendances->links() }}</div>
    @endif
  </div></div>
@endsection