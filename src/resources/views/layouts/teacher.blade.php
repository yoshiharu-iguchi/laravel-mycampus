@extends('layouts.base')

@section('title', trim($__env->yieldContent('page-title', '教員メニュー')).' | Teacher')

@push('head')
  {{-- 教員画面の共通スタイル（学生/保護者と統一） --}}
  <style>
    body { background:#f8f9fa; }
    .card { border-radius: 12px; }
    .display-6 { font-size: 1.8rem; }

    /* テーブル可読性：ヘッダー固定（任意） */
    .table thead th { position: sticky; top: 0; background: #fff; z-index: 1; }

    /* 幅を少しタイトにしたい場合（任意） */
    .container-narrow { max-width: 1120px; } /* 960→1100台に微調整 */
  </style>
@endpush

@section('topnav')
  @include('layouts.partials.topnav', [
    'role' => 'teacher',
    'skin' => 'dark',                 // 学生・保護者と同じダークスキン
    'brandRoute' => 'teacher.dashboard',
    'items' => [],
    'profileRoute' => 'teacher.profile.show',
    'logoutRoute' => 'logout',
  ])
@endsection

@section('content')
  <main class="container container-narrow py-4">
    {{-- ページ見出し（学生/保護者と同じトーン） --}}
    <div class="d-flex align-items-center justify-content-between mb-3"
         style="border-bottom:1px solid #e5e7eb; padding-bottom:.5rem;">
      <h1 class="h4 mb-0">@yield('page-title','教員メニュー')</h1>
      @hasSection('actions')
        <div class="d-flex gap-2">@yield('actions')</div>
      @endif
    </div>


    {{-- 子ビュー本体 --}}
    @yield('teacher-content')
  </main>
@endsection