@props([
  'title' => '科目別の出席・成績状況',
  'rows' => [],
  'showLegend' => true,    // 下部の凡例ON/OFF
])

{{-- Font Awesome（未読込なら一度だけ） --}}
@once
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endonce

<div class="card shadow-sm">
  <div class="card-header d-flex align-items-center gap-2">
    <span class="fw-semibold">
      <i class="fa-solid fa-table me-1"></i> {{ $title }}
    </span>
    {{-- アクション置き場：必要なら parent から <x-slot name="actions"> を差し込めます --}}
    @isset($actions)
      <div class="ms-auto d-none d-md-block">
        {{ $actions }}
      </div>
    @endisset
  </div>

  <div class="card-body p-0">
    @if (empty($rows))
      <div class="p-3 text-muted">表示できるデータがありません。</div>
    @else
      <div class="table-responsive">
        <table class="table table-sm table-hover table-bordered align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>科目コード</th>
              <th>科目名</th>
              <th class="text-end">出席</th>
              <th class="text-end">欠席</th>
              <th class="text-end">遅刻</th>
              <th class="text-end">公欠</th>
              <th class="text-end">未記録</th>
              <th class="text-end">出席率</th>
              <th class="text-end">平均点</th>
              <th class="text-end">最新点</th>
            </tr>
          </thead>
          <tbody>
          @foreach ($rows as $r)
            @php
              $rate = $r['attendanceRate'] ?? null;
              // 出席率の色分け（Bootstrap 5.3 の *-subtle を使用）
              $badgeClass = is_null($rate)
                ? 'bg-secondary-subtle text-secondary'
                : ($rate >= 90 ? 'bg-success-subtle text-success fw-semibold'
                : ($rate >= 70 ? 'bg-warning-subtle text-warning fw-semibold'
                               : 'bg-danger-subtle text-danger fw-semibold'));
            @endphp
            <tr>
              <td class="text-nowrap">{{ $r['subject_code'] ?? '-' }}</td>
              <td class="text-nowrap">{{ $r['subject_name'] ?? '(科目名なし)' }}</td>
              <td class="text-end">{{ $r['present'] ?? 0 }}</td>
              <td class="text-end">{{ $r['absent'] ?? 0 }}</td>
              <td class="text-end">{{ $r['late'] ?? 0 }}</td>
              <td class="text-end">{{ $r['excused'] ?? 0 }}</td>
              <td class="text-end">{{ $r['unrecorded'] ?? 0 }}</td>
              <td class="text-end">
                <span class="badge {{ $badgeClass }}">
                  {{ is_null($rate) ? '—' : number_format($rate, 1).'%' }}
                </span>
              </td>
              <td class="text-end">{{ is_null($r['avgScore'] ?? null) ? '—' : $r['avgScore'] }}</td>
              <td class="text-end">{{ is_null($r['latestScore'] ?? null) ? '—' : $r['latestScore'] }}</td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

  @if($showLegend)
    <div class="card-footer small text-muted">
      <i class="fa-solid fa-circle text-success me-1"></i>90%以上　
      <i class="fa-solid fa-circle text-warning me-1 ms-2"></i>70〜89%　
      <i class="fa-solid fa-circle text-danger me-1 ms-2"></i>69%以下　
      <span class="ms-2"><i class="fa-regular fa-circle text-secondary me-1"></i>— は未集計</span>
    </div>
  @endif
</div>