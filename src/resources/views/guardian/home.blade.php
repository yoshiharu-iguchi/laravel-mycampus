@extends('layouts.guardian')
@section('title','保護者ホーム | MyCampus')
@section('content')
<div class="container py-4">
  <h1 class="h4 mb-3">保護者ホーム</h1>

  @if(!$student)
    <div class="alert alert-info">お子さまの情報がまだ紐付いていません。</div>
  @else
    <div class="row g-3">
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h2 class="h6">（お子さま）出席サマリ</h2>
            <ul class="mb-0">
              <li>出席：{{ $attendanceSummary['present'] }}</li>
              <li>欠席：{{ $attendanceSummary['absent'] }}</li>
              <li>遅刻：{{ $attendanceSummary['late'] }}</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h2 class="h6">（お子さま）成績サマリ</h2>
            <ul class="mb-0">
              <li>科目数：{{ $gradeSummary['subjects'] }}</li>
              <li>平均：{{ $gradeSummary['avg_score'] }}</li>
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

