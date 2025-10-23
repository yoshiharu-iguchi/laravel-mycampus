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
    //'items' => [
    //  ['label'=>'ホーム',   'route'=>'student.home',              'icon'=>'house'],
    //  ['label'=>'プロフィール','route'=>'student.profile.show', 'icon'=>'user'],
    //],
  ])
@endsection


@section('content')
  <div class="page-header mb-3">
    @hasSection('page-title')
      <h1 class="h4 mb-0">@yield('page-title')</h1>
    @endif
  </div>


  {{-- 子ビューがここに内容を入れる --}}
  @yield('student-content')
@endsection