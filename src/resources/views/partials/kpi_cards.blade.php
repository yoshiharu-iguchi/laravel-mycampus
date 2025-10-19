@props([
  'kpi' => ['subjects' => 0],
  // 例: ['text' => '承認', 'class' => 'bg-success']（今は使っていなくてもOK）
  'transportBadge' => ['text' => '申請なし', 'class' => 'bg-secondary'],
  'link' => null, // バッジを押した時に飛ばす先（今は未使用でもOK）

  // ← 追加：非表示フラグ。例) ['subjects' => true] で「履修科目数」を隠す
  'hide' => [],
])

{{-- Font Awesome（未読み込みなら読み込む） --}}
@once
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endonce

<div class="row g-3 mb-4">
  {{-- ★ 追加：@unless で「隠す指示が無ければ表示」 --}}
  @unless(($hide['subjects'] ?? false))
    <div class="col">
      <div class="card shadow-sm text-center h-100">
        <div class="card-body">
          <div class="mb-2 text-secondary">
            <i class="fa-solid fa-book fa-lg me-1"></i> 履修科目数
          </div>
          {{-- 値（科目数）。$kpi['subjects'] が無ければ 0 を表示 --}}
          <div class="display-6 fw-bold">{{ $kpi['subjects'] ?? 0 }}</div>
        </div>
      </div>
    </div>
  @endunless
</div>