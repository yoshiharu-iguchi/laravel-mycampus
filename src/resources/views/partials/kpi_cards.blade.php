@props([
  'kpi' => ['subjects' => 0],
  // ← 未読件数ではなく、コントローラから渡すバッジ情報を受け取る
  // 例: ['text' => '承認', 'class' => 'bg-success']
  'transportBadge' => ['text' => '申請なし', 'class' => 'bg-secondary'],
  'link' => null, // バッジを押した時に飛ばす先（例: route('student.tr.create')）
])

{{-- Font Awesome（未読み込みなら読み込む） --}}
@once
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endonce

<div class="row g-3 mb-4">
  <div class="col">
    <div class="card shadow-sm text-center h-100">
      <div class="card-body">

        {{-- タイトル行：左=履修科目数、右=交通費申請のステータス・バッジ --}}
        <div class="mb-2 text-secondary">
          <i class="fa-solid fa-book fa-lg me-1"></i> 履修科目数
        </div>
        {{-- 値（科目数） --}}
        <div class="display-6 fw-bold">{{ $kpi['subjects'] ?? 0 }}</div>
      </div>
    </div>
  </div>
</div>