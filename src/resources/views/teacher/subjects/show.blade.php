@extends('layouts.teacher')
@section('title', ($subject->name_ja ?? $subject->name_en ?? '科目').' | 科目詳細')

@section('content')
@php
  // 表示名
  $displayName = $subject->name_ja ?? $subject->name_en ?? '名称未設定';

  // 開講期間（学期）を見やすい日本語に整形
  $termLabel = '-';
  $t = $subject->term;

  if ($t instanceof \App\Enums\Term) {
      $termLabel = $t->label();
  } else {
      $raw = is_string($t) ? trim($t) : (string)$t;
      $norm = mb_strtolower(str_replace([' ', '-', '　'], '', $raw)); // fullyear / full_year 等のゆらぎ吸収
      $map = [
        '1' => '前期', '2' => '後期', '3' => '通年',
        '前期' => '前期', '後期' => '後期', '通年' => '通年',
        'spring' => '前期', 'autumn' => '後期',
        'fullyear' => '通年', 'fullyear科目' => '通年', 'fullyear科目' => '通年',
        'fullyear' => '通年', 'fullyear' => '通年',
        'fullyear' => '通年', 'full_year' => '通年',
      ];
      $termLabel = $map[$norm] ?? ($map[$raw] ?? (is_numeric($t) ? ($map[(string)$t] ?? '-') : '-'));
  }

  // 必修/選択（英語/数値/真偽を日本語に正規化）
  $rawCat = $subject->category;

  if (is_string($rawCat)) {
      $key = mb_strtolower(trim($rawCat)); // 'elective' / 'required' など
  } elseif (is_bool($rawCat)) {
      $key = $rawCat ? 'true' : 'false';
  } elseif (is_numeric($rawCat)) {
      $key = (string)(int)$rawCat;         // 0/1
  } else {
      $key = '';
  }

  $catMap = [
      // 必修
      '必修' => '必修', '必須' => '必修', 'required' => '必修',
      'compulsory' => '必修', 'core' => '必修', '1' => '必修', 'true' => '必修',
      // 選択
      '選択' => '選択', 'elective' => '選択', 'optional' => '選択',
      '0' => '選択', 'false' => '選択',
  ];

  $reqOpt = $catMap[$key] ?? '-';

  // 定員
  $capacity = $subject->capacity ?? '—';

  // 説明
  $desc = $subject->description ?: '（説明は登録されていません）';
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
        <div class="fw-semibold">{{ rtrim(rtrim((string)$subject->credits, '0'), '.') ?? '—' }}</div>
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
        <div class="fw-semibold">{{ $subject->category_label }}</div>
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

{{-- 下位画面への導線 --}}
<div class="d-flex flex-wrap gap-2">
  <a class="btn btn-outline-primary btn-sm"
     href="{{ route('teacher.attendances.index',['subject'=>$subject->id,'date'=>now()->toDateString()]) }}">出席</a>
  <a class="btn btn-outline-success btn-sm"
     href="{{ route('teacher.grades.index',['subject'=>$subject->id]) }}">成績</a>
  <a class="btn btn-outline-dark btn-sm"
     href="{{ route('teacher.enrollments.index',['subject'=>$subject->id]) }}">履修</a>
</div>
@endsection