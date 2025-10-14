@extends('layouts.guardian')
@section('page-title','保護者ホーム')

@push('head')
<style>
  body { background:#f8f9fa; }
  .card { border-radius: 12px; }
  .display-6 { font-size: 1.8rem; }
</style>
@endpush

@section('guardian-content')
  @if(empty($student))
    <div class="alert alert-warning">お子様の登録がまだ完了していません。</div>
  @else
    <p class="text-muted mb-2">{{ $student->name }} さんの学習状況</p>
  
    @include('partials.subject_summary', ['title' => '科目別の出席・成績状況', 'rows' => $rows])
  @endif
@endsection
