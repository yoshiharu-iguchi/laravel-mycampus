@extends('layouts.student')
@section('title','成績一覧 | MyCampus')
@section('content')
  <div class="card mc-card"><div class="card-body">
    <h2 class="h5 mb-3">成績一覧</h2>
    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr><th>科目</th><th>担当</th><th>評価/点数</th><th>更新日</th></tr>
        </thead>
        <tbody>
          @forelse($grades as $g)
            @php
              $subjectName = $g->subject->name_ja ?? $g->subject->name_en ?? '—';
              $teacherName = $g->teacher->name ?? optional($g->subject->teacher)->name ?? '—';
              // 「score」か「evaluation」のどちらかが入っている想定。片方なければもう片方で表示
              $scoreOrEval = $g->score ?? $g->evaluation ?? null;
              // 日付は evaluation_date / recorded_at / updated_at の順に使用
              $dateRaw = $g->evaluation_date ?? $g->recorded_at ?? $g->updated_at ?? null;
              $date = $dateRaw ? \Illuminate\Support\Carbon::parse($dateRaw)->format('Y-m-d') : '—';
            @endphp
            <tr>
              <td>{{ $subjectName }}</td>
              <td>{{ $teacherName }}</td>
              <td>{{ $scoreOrEval ?? '—' }}</td>
              <td>{{ $date }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-muted">データがありません</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div></div>
@endsection