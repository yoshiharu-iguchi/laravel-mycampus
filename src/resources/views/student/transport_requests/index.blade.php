@extends('layouts.student')
@section('title','自分の申請一覧')
@section('student-content')
  <h1 class="h5 mb-3">自分の申請一覧</h1>
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      @if($requests->isEmpty())
        <p class="text-muted mb-0">まだ申請がありません。</p>
      @else
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead class="table-light"><tr>
              <th>申請日</th><th>施設</th><th>出発駅</th><th>到着駅</th><th>ステータス</th><th>URL</th>
            </tr></thead>
            <tbody>
              @foreach($requests as $r)
                <tr>
                  <td>{{ $r->created_at?->format('Y-m-d H:i') }}</td>
                  <td>{{ $r->facility->name ?? '-' }}</td>
                  <td>{{ $r->from_station_name }}</td>
                  <td>{{ $r->to_station_name }}</td>
                  <td><span class="badge text-bg-secondary">{{ $r->status }}</span></td>
                  <td>@if($r->search_url)<a href="{{ $r->search_url }}" target="_blank" rel="noopener">開く</a>@endif</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="mt-2">{{ $requests->links() }}</div>
      @endif
    </div>
  </div>
@endsection