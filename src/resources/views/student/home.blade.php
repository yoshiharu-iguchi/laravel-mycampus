@extends('layouts.student')
@section('page-title','学生ホーム')

@push('head')
<style>
  /* 追加の個別CSSは極力ここに寄せる。guardian も同じにする */
  body { background:#f8f9fa; }
  .card { border-radius: 12px; }
  .display-6 { font-size: 1.8rem; }
</style>
@endpush

@section('student-content')
  <p class="text-muted mb-2">ようこそ、{{ $student->name ?? '（学生名）' }} さん。</p>
  @include('partials.kpi_cards', [
    'kpi' => $kpi,
    'transportUnread' => $transportUnread,
    'link' => route('student.tr.index'),])
  @include('partials.subject_summary', ['title' => '科目別の出席・成績状況', 'rows' => $rows])
@endsection