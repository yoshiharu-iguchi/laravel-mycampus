@props([
  'kpi' => ['subjects' => 0],     
  'transportUnread' => 0,         
  'link' => null,                 
])

{{-- Font Awesome（未読み込みなら読み込む） --}}
@once
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endonce

<div class="row g-3 mb-4">
  <div class="col">
    <div class="card shadow-sm text-center h-100">
      <div class="card-body">
        {{-- タイトル行：左=履修科目数、右=メールアイコン＋未読バッジ --}}
        <div class="d-flex align-items-center justify-content-between mb-2 text-secondary">
          <div>
            <i class="fa-solid fa-book fa-lg me-1"></i> 履修科目数
          </div>

          @if($link)
            <a href="{{ $link }}" class="text-decoration-none position-relative">
              <i class="fa-solid fa-envelope"></i>
              @if(($transportUnread ?? 0) > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                  {{ $transportUnread > 99 ? '99+' : $transportUnread }}
                </span>
              @endif
            </a>
          @else
            <span class="position-relative">
              <i class="fa-solid fa-envelope"></i>
              @if(($transportUnread ?? 0) > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                  {{ $transportUnread > 99 ? '99+' : $transportUnread }}
                </span>
              @endif
            </span>
          @endif
        </div>

        {{-- 値（科目数） --}}
        <div class="display-6 fw-bold">{{ $kpi['subjects'] ?? 0 }}</div>
      </div>
    </div>
  </div>
</div>