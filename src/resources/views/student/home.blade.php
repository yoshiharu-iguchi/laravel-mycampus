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
    'kpi' => $kpi,])
   {{-- 交通費申請カード --}}
  <div class="row row-cols-1 row-cols-md-2 g-3 mb-3">
  <div class="col">
    <a href="{{ route('student.tr.create') }}" class="text-decoration-none">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          {{-- ★ タイトル行にバッジを追加 --}}
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
</div>
  @include('partials.subject_summary', ['title' => '科目別の出席・成績状況', 'rows' => $rows])
@endsection