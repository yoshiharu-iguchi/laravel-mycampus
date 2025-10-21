@extends('layouts.guardian')
@section('page-title','出席状況')

@section('guardian-content')
  <div class="mb-2 text-muted">
    対象学生：{{ $student->name ?? '—' }}
  </div>

  {{-- 学生と同じUIのコンポーネントを再利用 --}}
  <x-attendance.subject-summary
    title="科目別の出席状況"
    :rows="$rows"
    :showLegend="true"
    :showScore="false"
  />
@endsection