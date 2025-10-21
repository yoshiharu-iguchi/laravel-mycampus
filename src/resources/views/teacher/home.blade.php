@extends('layouts.teacher')

@section('title','教員ホーム | MyCampus')

@push('head')
<style>
  body { background:#f8f9fa; }
  .card { border-radius: 12px; }
  .nav-card .card-body { min-height: 90px; }
</style>
@endpush

@section('content')
  <div class="row g-3 mb-3">
    <div class="col-12">
      <div class="card p-3">
        <h1 class="h5 mb-1"><i class="bi bi-house-door me-1"></i> 教員ホーム</h1>
        <p class="text-muted mb-0">ようこそ、{{ Auth::guard('teacher')->user()->name }} さん。</p>
      </div>
    </div>
  </div>

  {{-- ✅ KPI（学生/保護者と同じパーシャル）--}}
  @include('partials.kpi_cards', ['kpi' => $kpi])

  {{-- ナビ（カードリンク） --}}
  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
    <div class="col nav-card">
      <a href="{{ route('teacher.subjects.index') }}" class="text-decoration-none">
        <div class="card p-3 h-100">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-people fs-4"></i>
            <div>
              <div class="fw-semibold">履修一覧</div>
              <div class="text-muted small">担当科目の履修状況を確認</div>
            </div>
          </div>
        </div>
      </a>
    </div>

    <div class="col nav-card">
      <a href="{{ route('teacher.subjects.index') }}" class="text-decoration-none">
        <div class="card p-3 h-100">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-check2-square fs-4"></i>
            <div>
              <div class="fw-semibold">出欠管理</div>
              <div class="text-muted small">出席簿・一括更新</div>
            </div>
          </div>
        </div>
      </a>
    </div>

    <div class="col nav-card">
      <a href="{{ route('teacher.subjects.index') }}" class="text-decoration-none">
        <div class="card p-3 h-100">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-card-checklist fs-4"></i>
            <div>
              <div class="fw-semibold">成績管理</div>
              <div class="text-muted small">評価の一覧と一括更新</div>
            </div>
          </div>
        </div>
      </a>
    </div>

    <div class="col nav-card">
      <a href="{{ route('teacher.facilities.index') }}" class="text-decoration-none">
        <div class="card p-3 h-100">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-hospital fs-4"></i>
            <div>
              <div class="fw-semibold">実習施設</div>
              <div class="text-muted small">施設の新規登録・編集</div>
            </div>
          </div>
        </div>
      </a>
    </div>
  </div>
@endsection