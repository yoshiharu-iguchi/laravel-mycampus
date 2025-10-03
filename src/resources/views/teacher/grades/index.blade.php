{{-- resources/views/teacher/grades/index.blade.php --}}
@extends('layouts.teacher')

@section('title','成績管理 | MyCampus')

@section('content')
  @php
    // 現在の科目・評価日（GETのクエリ or Controllerで渡した値）
    $currentSubjectId = request('subject_id') ?? ($subject->id ?? null);
    $currentDate      = ($date ?? request('evaluation_date') ?? request('date')) ?: now()->toDateString();

    // $grades を表示用配列に標準化
    $rows = isset($rows) && is_iterable($rows) ? collect($rows) : collect($grades ?? []);
    $rows = $rows->map(function($r){
      return [
        'id'           => data_get($r,'id'),
        'student_name' => data_get($r,'student_name', data_get($r,'student.name','')),
        'score'        => data_get($r,'score'), // 0-100 or null
        'note'         => data_get($r,'note',''),
      ];
    })->values()->all();
  @endphp

  {{-- バリデーションエラー表示（任意） --}}
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h1 class="h5 mb-0"><i class="bi bi-card-checklist me-2"></i>成績管理</h1>

        {{-- 絞り込み（GET）: 名前は evaluation_date に統一 --}}
        <form method="GET" class="d-flex gap-2">
          <select name="subject_id" class="form-select form-select-sm" style="min-width: 220px;">
            <option value="">すべての科目</option>
            @foreach(($subjects ?? []) as $sub)
              <option value="{{ $sub->id }}" @selected((string)$currentSubjectId === (string)$sub->id)>
                {{ $sub->name_ja ?? $sub->name ?? '科目' }}
              </option>
            @endforeach
          </select>
          <input type="date" name="evaluation_date" value="{{ $currentDate }}" class="form-control form-control-sm" style="min-width: 160px;">
          <button class="btn btn-sm btn-outline-secondary" type="submit">絞り込み</button>
        </form>
      </div>

      {{-- 一括保存（POST）: hidden も evaluation_date --}}
      <form method="POST" action="{{ route('teacher.grades.bulkUpdate') }}">
        @csrf
        <input type="hidden" name="subject_id"      value="{{ $currentSubjectId }}">
        <input type="hidden" name="evaluation_date" value="{{ $currentDate }}">

        <div class="table-responsive">
          <table class="table table-sm align-middle mb-3">
            <thead class="table-light">
              <tr>
                <th>学生</th>
                <th style="width: 160px;">得点（0-100）</th>
                <th>講評</th>
              </tr>
            </thead>
            <tbody>
              @forelse($rows as $i => $row)
                <tr>
                  <td class="text-nowrap">
                    <input type="hidden" name="rows[{{ $i }}][id]" value="{{ $row['id'] ?? '' }}">
                    {{ $row['student_name'] ?? '—' }}
                  </td>
                  <td>
                    <input type="number" name="rows[{{ $i }}][score]" class="form-control form-control-sm"
                           min="0" max="100" step="1" value="{{ $row['score'] }}" placeholder="例：80">
                  </td>
                  <td>
                    <input type="text" name="rows[{{ $i }}][note]" class="form-control form-control-sm"
                           value="{{ $row['note'] ?? '' }}" placeholder="講評（任意）">
                  </td>
                </tr>
              @empty
                <tr><td colspan="3" class="text-center text-muted py-4">表示する成績データがありません。</td></tr>
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