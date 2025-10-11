@props(['kpi' => ['subjects'=>0,'avgScoreOverall'=>null,'presentTotal'=>0]])

{{-- Font Awesome CDN（未読み込みの場合） --}}
@once
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endonce

<div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
  {{-- 履修科目数 --}}
  <div class="col">
    <div class="card shadow-sm text-center h-100">
      <div class="card-body">
        <div class="mb-2 text-secondary">
          <i class="fa-solid fa-book fa-lg me-1"></i> 履修科目数
        </div>
        <div class="display-6 fw-bold">{{ $kpi['subjects'] ?? 0 }}</div>
      </div>
    </div>
  </div>

  {{-- 平均スコア（色分け付き） --}}
  <div class="col">
    <div class="card shadow-sm text-center h-100">
      <div class="card-body">
        <div class="mb-2 text-secondary">
          <i class="fa-solid fa-chart-line fa-lg me-1"></i> 平均スコア
        </div>
        @php
          $score = $kpi['avgScoreOverall'] ?? null;
          $scoreColor = is_null($score) ? 'text-muted' :
                        ($score >= 80 ? 'text-success fw-bold' :
                        ($score >= 60 ? 'text-warning fw-bold' : 'text-danger fw-bold'));
        @endphp
        <div class="display-6 {{ $scoreColor }}">
          {{ is_null($score) ? '—' : $score }}
        </div>
      </div>
    </div>
  </div>

  {{-- 出席数（合計） --}}
  <div class="col">
    <div class="card shadow-sm text-center h-100">
      <div class="card-body">
        <div class="mb-2 text-secondary">
          <i class="fa-solid fa-user-check fa-lg me-1"></i> 出席数（合計）
        </div>
        <div class="display-6 fw-bold">{{ $kpi['presentTotal'] ?? 0 }}</div>
      </div>
    </div>
  </div>
</div>