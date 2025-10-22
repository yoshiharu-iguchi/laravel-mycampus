@extends('layouts.student')
@section('page-title','Student home')

@push('head')
<style>
  body { background:#f8f9fa; }
  .card { border-radius: 12px; }
  .display-6 { font-size: 1.8rem; }
  .nav-card .card-body { min-height: 90px; }
</style>
@endpush

@section('student-content')
  <p class="text-muted mb-2">ようこそ、{{ $student->name ?? '（学生名）' }} さん。</p>

  {{-- ✅ KPI：履修科目数カードを隠す（hideスイッチを渡す） --}}
  @include('partials.kpi_cards', [
    'kpi' => $kpi,
    'hide' => ['subjects' => true],
  ])

  {{-- ホームナビ（カードリンク） --}}
  <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 mb-4">

    {{-- 履修登録 --}}
    <div class="col nav-card">
      <a href="{{ route('student.enrollments.index') }}" class="text-decoration-none">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <div class="mb-1 text-secondary">📚 履修登録</div>
            <div class="small text-muted">登録済みの確認・取消</div>
          </div>
        </div>
      </a>
    </div>

    {{-- 科目一覧 --}}
    <div class="col nav-card">
      <a href="{{ route('student.subjects.index') }}" class="text-decoration-none">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <div class="mb-1 text-secondary">🧑‍🏫 科目一覧</div>
            <div class="small text-muted">シラバス確認・履修登録</div>
          </div>
        </div>
      </a>
    </div>

    {{-- 出席状況 --}}
    <div class="col nav-card">
      <a href="{{ route('student.attendances.index') }}" class="text-decoration-none">
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
      <a href="{{ route('student.grades.index') }}" class="text-decoration-none">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <div class="mb-1 text-secondary">🧮 成績</div>
            <div class="small text-muted">科目別の評点・平均など</div>
          </div>
        </div>
      </a>
    </div>

    {{-- 交通費申請 --}}
    <div class="col nav-card">
      <a href="{{ route('student.tr.create') }}" class="text-decoration-none">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <div class="mb-1 text-secondary d-flex align-items-center justify-content-between">
              <span>🚌 交通費申請</span>
              <span class="badge rounded-pill {{ $transportBadge['class'] ?? 'bg-secondary' }}">
                {{ $transportBadge['text'] ?? '申請なし' }}
              </span>
            </div>
            <div class="small text-muted">経路検索・申請フォーム</div>
          </div>
        </div>
      </a>
    </div>

    {{-- 実習施設一覧（必要でなければ後で外せます） --}}
    <div class="col nav-card">
      <a href="{{ route('student.facilities.index') }}" class="text-decoration-none">
        <div class="card shadow-sm h-100">
          <div class="card-body">
            <div class="mb-1 text-secondary">🏥 施設一覧</div>
            <div class="small text-muted">実習施設の情報を確認</div>
          </div>
        </div>
      </a>
    </div>

    {{-- ❌ プロフィールカード → 削除（既にナビにあるため） --}}
    {{-- ❌ 学習状況カード → 削除 --}}
  </div>

  {{-- ❌ 科目サマリー（出席・成績） → 削除 --}}
  {{-- @include('partials.subject_summary', ['title' => '科目別の出席・成績状況', 'rows' => $rows]) --}}
@endsection