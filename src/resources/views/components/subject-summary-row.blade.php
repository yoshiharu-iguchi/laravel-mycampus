@props(['r'])
<tr>
  <td>{{ $r['subject_code'] }}</td>
  <td>{{ $r['subject_name'] }}</td>
  <td class="text-end">{{ $r['present'] }}</td>
  <td class="text-end">{{ $r['absent'] }}</td>
  <td class="text-end">{{ $r['late'] }}</td>
  <td class="text-end">{{ $r['excused'] }}</td>
  <td class="text-end">{{ $r['unrecorded'] }}</td>

  {{-- 出席率（プログレスバー） --}}
  <td class="text-end">
    @if (is_null($r['attendanceRate']))
      —
    @else
      <div class="d-flex flex-column align-items-end">
        <small class="text-muted">{{ $r['attendanceRate'] }}%</small>
        <div class="progress" style="width:120px;">
          <div class="progress-bar" role="progressbar"
               style="width: {{ $r['attendanceRate'] }}%;"
               aria-valuenow="{{ $r['attendanceRate'] }}" aria-valuemin="0" aria-valuemax="100">
          </div>
        </div>
      </div>
    @endif
  </td>

  {{-- スコア（色分け） --}}
  @php
    $avg = $r['avgScore']; $latest = $r['latestScore'];
    $avgClass = is_null($avg) ? '' : ($avg >= 80 ? 'text-success fw-semibold' : ($avg < 60 ? 'text-danger' : 'text-body'));
    $latestClass = is_null($latest) ? '' : ($latest >= 80 ? 'text-success fw-semibold' : ($latest < 60 ? 'text-danger' : 'text-body'));
  @endphp
  <td class="text-end"><span class="{{ $avgClass }}">{{ is_null($avg) ? '—' : $avg }}</span></td>
  <td class="text-end"><span class="{{ $latestClass }}">{{ is_null($latest) ? '—' : $latest }}</span></td>
</tr>
