@extends('layouts.student')
@section('title','成績一覧 | MyCampus')

@push('head')
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endpush

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
              $score      = is_numeric($g->score ?? null) ? (int)$g->score : null;
              $evaluation = $g->evaluation ?? null;
              $dateRaw    = $g->evaluation_date ?? $g->recorded_at ?? $g->updated_at ?? null;
            @endphp
            <tr>
              <td class="text-nowrap">{{ $subjectName }}</td>
              <td class="text-nowrap">{{ $teacherName }}</td>
              <td class="text-center">
                <x-grade.score-badge :score="$score" :evaluation="$evaluation" :high="80" :mid="60" />
              </td>
              <td class="text-nowrap">{{ optional($dateRaw)->format('Y-m-d') ?? '—' }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-muted">データがありません</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- ← ここが「テーブル直下」：凡例を配置 --}}
    <div class="small text-muted mt-2">
      <i class="fa-solid fa-circle text-success"></i> 80〜　
      <i class="fa-solid fa-circle text-warning"></i> 60〜79　
      <i class="fa-solid fa-circle text-danger"></i> 0〜59　
      <i class="fa-regular fa-circle text-secondary"></i> 未入力
    </div>

  </div></div>
@endsection