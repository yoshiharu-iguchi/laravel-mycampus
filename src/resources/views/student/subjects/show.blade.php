@extends('layouts.student')
@section('title', ($subject->name_ja ?? $subject->name_en ?? '科目').' | 科目詳細')
@section('content')

{{-- fixed-top ナビ対策の上余白 --}}
<div class="pt-4"></div>

{{-- === 科目詳細カード（教師側と同テイスト） === --}}
@php
  // 表示名
  $displayName = $subject->name_ja ?? $subject->name_en ?? '名称未設定';

  // 開講期間（学期）ラベル（Subjectにterm_labelアクセサがあれば優先）
  if (isset($subject->term_label)) {
      $termLabel = $subject->term_label;
  } else {
      $termLabel = '-';
      $t = $subject->term;
      if ($t instanceof \App\Enums\Term) {
          $termLabel = $t->label();
      } else {
          $raw  = is_string($t) ? trim($t) : (string)$t;
          $norm = mb_strtolower(str_replace([' ', '-', '　'], '', $raw));
          $termLabel = match ($norm) {
              '1','spring','前期' => '前期',
              '2','autumn','後期' => '後期',
              '3','fullyear','fullyear科目','full_year','通年' => '通年',
              default => is_numeric($raw) ? (['1'=>'前期','2'=>'後期','3'=>'通年'][(string)$raw] ?? '-') : '-',
          };
      }
  }

  // 必修/選択ラベル（Subjectにcategory_labelアクセサがあれば優先）
  if (isset($subject->category_label)) {
      $categoryLabel = $subject->category_label;
  } else {
      $rawCat = $subject->category;
      if (is_string($rawCat))       $key = mb_strtolower(trim($rawCat));
      elseif (is_bool($rawCat))     $key = $rawCat ? 'true' : 'false';
      elseif (is_numeric($rawCat))  $key = (string)(int)$rawCat; // 0/1
      else                          $key = '';
      $categoryLabel = match ($key) {
          '必修','必須','required','compulsory','core','1','true' => '必修',
          '選択','elective','optional','0','false'                 => '選択',
          default => '—',
      };
  }

  $capacity = $subject->capacity ?? '—';
  $desc     = $subject->description ?: '（説明は登録されていません）';
@endphp

<h1 class="h5 mb-3">科目詳細</h1>

<div class="card mb-3">
  <div class="card-body">
    <div class="row gy-2">
      <div class="col-12 col-md-6">
        <div class="text-muted small">ID</div>
        <div class="fw-semibold">{{ $subject->id }}</div>
      </div>
      <div class="col-12 col-md-6">
        <div class="text-muted small">科目コード</div>
        <div class="fw-semibold">{{ $subject->subject_code ?? '—' }}</div>
      </div>

      <div class="col-12">
        <div class="text-muted small">科目名</div>
        <div class="fw-semibold">{{ $displayName }}</div>
      </div>

      <div class="col-6 col-md-3">
        <div class="text-muted small">単位</div>
        <div class="fw-semibold">{{ rtrim(rtrim((string)$subject->credits, '0'), '.') ?: '—' }}</div>
      </div>
      <div class="col-6 col-md-3">
        <div class="text-muted small">年度</div>
        <div class="fw-semibold">{{ $subject->year ?? '—' }}</div>
      </div>
      <div class="col-6 col-md-3">
        <div class="text-muted small">開講期間</div>
        <div class="fw-semibold">{{ $termLabel }}</div>
      </div>
      <div class="col-6 col-md-3">
        <div class="text-muted small">必修/選択</div>
        <div class="fw-semibold">
          @php $isRequired = ($categoryLabel === '必修'); @endphp
          <span class="badge {{ $isRequired ? 'text-bg-danger' : 'text-bg-secondary' }}">{{ $categoryLabel }}</span>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="text-muted small">定員</div>
        <div class="fw-semibold">{{ $capacity }}</div>
      </div>

      <div class="col-12 mt-2">
        <div class="text-muted small">説明</div>
        <div>{{ $desc }}</div>
      </div>
    </div>
  </div>
</div>

{{-- 履修登録カード --}}
<div class="card">
  <div class="card-header">履修手続き</div>
  <div class="card-body">
    @if($enrollment)
      {{-- ★履修中：取消ボタンのみ表示 --}}
      <form method="POST" action="{{ route('student.enrollments.destroy', $enrollment) }}"
            onsubmit="return confirm('この履修を取り消しますか？')">
        @csrf
        @method('DELETE')
        <input type="hidden" name="return_to" value="{{ url()->full() }}">
        <button class="btn btn-outline-danger">履修を取り消す</button>
      </form>
      <span class="badge text-bg-success ms-2 align-middle">履修中</span>
    @else
      {{-- ★未履修：登録フォームを表示（学期は未選択でもOK） --}}
      <form method="post" action="{{ route('student.enrollments.store') }}" class="mt-3">
        @csrf
        <input type="hidden" name="subject_id" value="{{ $subject->id }}">

        <div class="row g-2 align-items-end">
          <div class="col-auto">
            <label class="form-label">年度</label>
            <input type="number" name="year"
                   class="form-control form-control-sm"
                   value="{{ old('year', $subject->year ?? now()->year) }}" required>
          </div>

          <div class="col-auto">
            <label class="form-label">学期</label>
            <select name="term" class="form-select form-select-sm">
              <option value="" @selected(old('term')===null || old('term')==='')>（選択しない）</option>
              @foreach(\App\Enums\Term::cases() as $t)
                <option value="{{ $t->value }}" @selected((string)old('term') === (string)$t->value)>
                  {{ $t->label() }}
                </option>
              @endforeach
            </select>
          </div>

          <div class="col-auto">
            <button class="btn btn-primary btn-sm">履修登録</button>
          </div>
        </div>
      </form>
    @endif
  </div>
</div>

{{-- 戻るリンク --}}
<div class="mt-3">
  <a href="{{ route('student.subjects.index') }}" class="btn btn-outline-secondary">科目一覧へ戻る</a>
</div>

@endsection

