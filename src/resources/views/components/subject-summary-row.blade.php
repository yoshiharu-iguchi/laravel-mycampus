@props(['r'])

@php
  $rate = $r['attendanceRate'] ?? null;
  $rateClass = is_null($rate)
    ? ''
    : ($rate >= 90 ? 'text-success fw-semibold'
       : ($rate < 70 ? 'text-danger fw-semibold' : ''));
@endphp

<tr>
  <td class="text-nowrap">{{ $r['subject_code'] ?? '-' }}</td>
  <td class="text-nowrap">{{ $r['subject_name'] ?? '(科目名なし)' }}</td>
  <td class="text-end">{{ $r['present'] ?? 0 }}</td>
  <td class="text-end">{{ $r['absent'] ?? 0 }}</td>
  <td class="text-end">{{ $r['late'] ?? 0 }}</td>
  <td class="text-end">{{ $r['excused'] ?? 0 }}</td>
  <td class="text-end">{{ $r['unrecorded'] ?? 0 }}</td>
  <td class="text-end {{ $rateClass }}">
    {{ is_null($rate) ? '—' : number_format($rate,1).'%' }}
  </td>
  <td class="text-end">{{ is_null($r['avgScore'] ?? null) ? '—' : $r['avgScore'] }}</td>
  <td class="text-end">{{ is_null($r['latestScore'] ?? null) ? '—' : $r['latestScore'] }}</td>
</tr>
