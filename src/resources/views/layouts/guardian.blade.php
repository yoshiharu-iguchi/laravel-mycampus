@extends('layouts.base')

@section('title', (trim($__env->yieldContent('title', '保護者ホーム')) ?: '保護者ホーム').' | Guardian')

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
    'role' => 'guardian',
    'skin' => 'dark',                  // ← student と揃える
    'logoutRoute' => 'logout',
    'items' => [
      ['label'=>'ホーム',   'route'=>'guardian.home',              'icon'=>'house'],
      ['label'=>'出席',     'route'=>'guardian.attendances.index','icon'=>'calendar-check'],
      ['label'=>'成績',     'route'=>'guardian.grades.index',     'icon'=>'chart-column'],
      ['label'=>'プロフィール','route'=>'guardian.profile.show', 'icon'=>'user'],
    ],
  ])
@endsection

@section('content')
  <div class="page-header mb-3">
    <h1 class="h4 mb-0">@yield('page-title', '保護者メニュー')</h1>
    @hasSection('actions')
      <div class="d-flex align-items-center gap-2">@yield('actions')</div>
    @endif
  </div>

  @includeFirst(['layouts.partials.flash','partials.flash'])
  @includeFirst(['layouts.partials.errors','partials.errors'])

  @yield('guardian-content')
@endsection