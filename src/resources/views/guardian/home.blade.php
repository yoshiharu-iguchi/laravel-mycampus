@extends('layouts.guardian')
@section('title','保護者ホーム | MyCampus')
@section('content')
<div class="container py-4">
  <h1 class="h4 mb-3">保護者ホーム</h1>
  <h2 class="h6 mt-3">学生：{{ $student->name ?? ($child->name ?? '不明') }}</h2>

  @if(!$student)
    <div class="alert alert-info">お子さまの情報がまだ紐付いていません。</div>
  @else
    <div class="row g-3">
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h2 class="h6">（お子さま）出席サマリ</h2>
            <ul class="mb-0">
              <li>出席：{{ (int)($attendanceSummary['present'] ?? 0) }}</li>
              <li>欠席：{{ (int)($attendanceSummary['absent'] ?? 0) }}</li>
              <li>遅刻：{{ (int)($attendanceSummary['late'] ?? 0) }}</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h2 class="h6">（お子さま）成績サマリ</h2>
            <ul class="mb-0">
            <li>科目数：{{ (int)($gradeSummary['subjects'] ?? 0) }}</li>
            <li>
              平均：
            @if(isset($gradeSummary['avg_score']) && is_numeric($gradeSummary['avg_score']))
              {{ number_format($gradeSummary['avg_score'], 1) }}
            @else
            -
            @endif
          </li>
        </ul>
          </div>
        </div>
      </div>
    </div>
  @endif

  <div class="mt-3 d-flex gap-2">
    <a class="btn btn-secondary" href="{{ route('guardian.profile.show') }}">プロフィールを見る</a>
  </div>
</div>
@endsection

