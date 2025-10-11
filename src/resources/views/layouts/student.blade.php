@extends('layouts.base')

@section('title', (trim($__env->yieldContent('title', '学生ホーム')) ?: '学生ホーム').' | Student')

@push('head')
  <style>
    .page-header{
      display:flex; gap:16px; align-items:center; justify-content:space-between;
      border-bottom:1px solid #e5e7eb; padding-bottom:.5rem;
    }
  </style>
@endpush

@section('topnav')
  @include('layouts.partials.topnav', [
    'role' => 'student',
    'skin' => 'dark',                 // ← guardian と揃える
    'logoutRoute' => 'logout',        // 共通POST logout
    'items' => [
      ['label'=>'ホーム',   'route'=>'student.home',              'icon'=>'house'],
      ['label'=>'出席',     'route'=>'student.attendances.index','icon'=>'calendar-check'],
      ['label'=>'成績',     'route'=>'student.grades.index',     'icon'=>'chart-column'],
      ['label'=>'プロフィール','route'=>'student.profile.show', 'icon'=>'user'],
    ],
  ])
@endsection


@section('content')
  <div class="page-header mb-3">
    <h1 class="h4 mb-0">@yield('page-title', '学生メニュー')</h1>
    @hasSection('actions')
      <div class="d-flex align-items-center gap-2">@yield('actions')</div>
    @endif
  </div>

  @includeFirst(['layouts.partials.flash','partials.flash'])
  @includeFirst(['layouts.partials.errors','partials.errors'])

  {{-- 子ビューがここに内容を入れる --}}
  @yield('student-content')
@endsection