@props(['grades', 'title' => '成績一覧', 'high' => 80, 'mid' => 60])

<div class="card mc-card">
  <div class="card-body">
    <h2 class="h5 mb-3">{{ $title }}</h2>
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
                <x-grade.score-badge :score="$score" :evaluation="$evaluation" :high="$high" :mid="$mid" />
              </td>
              <td class="text-nowrap">{{ optional($dateRaw)->format('Y-m-d') ?? '—' }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-muted">データがありません</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="small text-muted mt-2">
      <i class="fa-solid fa-circle text-success"></i> 80〜　
      <i class="fa-solid fa-circle text-warning"></i> 60〜79　
      <i class="fa-solid fa-circle text-danger"></i> 0〜59　
      <i class="fa-regular fa-circle text-secondary"></i> 未入力
    </div>
  </div>
</div>