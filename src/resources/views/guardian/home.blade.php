@extends('layouts.guardian')
@section('page-title','保護者ホーム')

@push('head')
<style>
  body { background:#f8f9fa; }
  .card { border-radius: 12px; }
  .display-6 { font-size: 1.8rem; }
  .nav-card .card-body { min-height: 90px; }
</style>
@endpush

@section('guardian-content')
  @php
    $g = $guardian ?? auth('guardian')->user();
    $s = $student  ?? optional($g)->student;
  @endphp

  <p class="text-muted mb-2">
    ようこそ、{{ $g->name ?? '（保護者名）' }} さん。
    <span class="ms-2">対象学生：{{ optional($s)->name ?? '—' }}</span>
  </p>

  {{-- KPIカードがある場合は使う／無ければこのブロックは削除可 --}}
  @isset($kpi)
    @include('partials.kpi_cards', [
      'kpi' => $kpi,
      'hide' => ['subjects' => true], 
    ])
  @endisset

  {{-- ホームナビ（カードリンク） --}}
  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 mb-4">

    {{-- 出席状況 --}}
    <div class="col nav-card">
      <a href="{{ route('guardian.attendances.index') }}" class="text-decoration-none">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <div class="mb-1 text-secondary">📝 出席状況</div>
            <div class="small text-muted">各科目の出欠状況を一覧で確認</div>
          </div>
        </div>
      </a>
    </div>

    {{-- 成績 --}}
    <div class="col nav-card">
      <a href="{{ route('guardian.grades.index') }}" class="text-decoration-none">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <div class="mb-1 text-secondary">🧮 成績</div>
            <div class="small text-muted">科目別の評点・最新更新を確認</div>
          </div>
        </div>
      </a>
    </div>

    {{-- 学習状況/進捗ダッシュボード --}}
    <div class="col nav-card">
      <a href="{{ route('guardian.progress.index') }}" class="text-decoration-none">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <div class="mb-1 text-secondary">📊 学習状況</div>
            <div class="small text-muted">出席率や得点のサマリー</div>
          </div>
        </div>
      </a>
    </div>

    {{-- ※ 学生ホームにある「交通費申請」「実習施設一覧」は表示しません --}}
  </div>
@endsection
