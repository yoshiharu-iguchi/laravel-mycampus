{{-- resources/views/teacher/attendances/index.blade.php --}}
@extends('layouts.teacher')

@section('title','出欠管理 | MyCampus')

@section('content')
  @php
    // 現在の科目・日付（GETのクエリ or Controllerで渡した値）
    $currentSubjectId = request('subject_id') ?? ($subject->id ?? null);
    $currentDate      = ($date ?? request('date')) ?: now()->toDateString();

    // $rows が無ければ $attendances を汎用配列に整形
    $rows = isset($rows) && is_iterable($rows) ? collect($rows) : collect($attendances ?? []);
    $rows = $rows->map(function($r){
      $date = data_get($r,'date');
      if ($date instanceof \Carbon\Carbon) {
        $date = $date->format('Y-m-d');
      } elseif (!is_string($date)) {
        $date = '';
      }
      return [
        'id'           => data_get($r,'id'),
        'date'         => $date,
        'student_name' => data_get($r,'student_name', data_get($r,'student.name','')),
        'status'       => (string) data_get($r,'status','4'), // 4=未記録
        'note'         => data_get($r,'note',''),
      ];
    })->values()->all();
  @endphp

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h1 class="h5 mb-0"><i class="bi bi-check2-square me-2"></i>出欠管理</h1>
        {{-- 絞り込み（GET） --}}
        <form method="GET" class="d-flex gap-2">
          <select name="subject_id" class="form-select form-select-sm" style="min-width: 220px;">
            <option value="">すべての科目</option>
            @foreach(($subjects ?? []) as $sub)
              <option value="{{ $sub->id }}" @selected((string)$currentSubjectId === (string)$sub->id)>
                {{ $sub->name_ja ?? $sub->name ?? '科目' }}
              </option>
            @endforeach
          </select>
          <input type="date" name="date" value="{{ $currentDate }}" class="form-control form-control-sm" style="min-width: 160px;">
          <button class="btn btn-sm btn-outline-secondary" type="submit">絞り込み</button>
        </form>
      </div>

      {{-- 一括保存（POST） --}}
      <form method="POST" action="{{ route('teacher.attendances.bulkUpdate') }}">
        @csrf

        {{-- bulkUpdate のバリデーション要件に合わせて hidden を送る --}}
        <input type="hidden" name="subject_id" value="{{ $currentSubjectId }}">
        <input type="hidden" name="date"       value="{{ $currentDate }}">

        <div class="table-responsive">
          <table class="table table-sm align-middle mb-3">
            <thead class="table-light">
              <tr>
                <th style="width: 160px;">日付</th>
                <th>学生</th>
                <th style="width: 180px;">ステータス</th>
                <th>備考</th>
              </tr>
            </thead>
            <tbody>
              @forelse($rows as $i => $row)
                <tr>
                  <td>
                    <input type="hidden" name="rows[{{ $i }}][id]" value="{{ $row['id'] ?? '' }}">
                    <input type="date"  name="rows[{{ $i }}][date]" class="form-control form-control-sm" value="{{ $row['date'] ?? '' }}">
                  </td>
                  <td class="text-nowrap">{{ $row['student_name'] ?? '—' }}</td>
                  <td>
                    @php $s = (string)($row['status'] ?? '4'); @endphp
                    <select name="rows[{{ $i }}][status]" class="form-select form-select-sm">
                      <option value="1" @selected($s==='1')>出席</option>
                      <option value="2" @selected($s==='2')>遅刻</option>
                      <option value="3" @selected($s==='3')>早退</option>
                      <option value="0" @selected($s==='0')>欠席</option>
                      <option value="4" @selected($s==='4')>未記録</option>
                    </select>
                  </td>
                  <td>
                    <input type="text" name="rows[{{ $i }}][note]" class="form-control form-control-sm" value="{{ $row['note'] ?? '' }}" placeholder="メモ（任意）">
                  </td>
                </tr>
              @empty
                <tr><td colspan="4" class="text-center text-muted py-4">表示する出席データがありません。</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <div class="text-muted small">表示件数：{{ count($rows) }}件</div>
          <button type="submit" class="btn btn-dark btn-sm">
            <i class="bi bi-save me-1"></i>一括保存
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection