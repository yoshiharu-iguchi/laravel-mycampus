@extends('layouts.guardian')
@section('page-title','保護者ホーム')

@push('head')
<style>
  body { background:#f8f9fa; }
  .card { border-radius: 12px; }
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

  {{-- ホームナビ（カードリンク） --}}
  <div class="row row-cols-1 row-cols-md-2 g-3 mb-4">

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
            <div class="small text-muted">科目別の評点・更新履歴を確認</div>
          </div>
        </div>
      </a>
    </div>

  </div>

  {{-- ※ 学習状況カードは表示しません（学生と同様の2カード構成） --}}
  {{-- ※ 交通費申請・実習施設一覧も表示しません --}}
@endsection
