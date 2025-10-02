@extends('layouts.base')

@section('title', trim($__env->yieldContent('title', 'Student Menu')).' | Student')

@push('head')
  <style>
    /* adminレイアウトのページヘッダ風 */
    .page-header{
      display:flex; gap:16px; align-items:center; justify-content:space-between;
      border-bottom:1px solid #e5e7eb; padding-bottom:.5rem;
    }
  </style>
@endpush

@section('topnav')
  @include('layouts.partials.topnav', [
    'role' => 'student',
    'logoutRoute' => 'student.logout',
    'skin' => 'admin', // Admin と同じダークスキン
    'items' => [
      ['label'=>'Dashboard','route'=>'student.home','icon'=>'house-door'],
      ['label'=>'Search & Request','route'=>'student.tr.create', 'badge'=>$trCount ?? null,'icon'=>'geo-alt'],
      ['label'=>'Requests','route'=>'student.tr.index','icon'=>'list-check'],
      ['label'=>'Facility','route'=>'student.facilities.index','icon'=>'building'],
      ['label'=>'Profile','route'=>'student.profile.show','icon'=>'person-circle'],
    ],
  ])
@endsection

@section('content')
  <div class="page-header mb-3">
    <h1 class="h4 mb-0">@yield('title','学生メニュー')</h1>

    {{-- adminの @yield('actions') 相当。student側はどちらでも記述可 --}}
    @hasSection('actions')
      <div class="d-flex align-items-center gap-2">@yield('actions')</div>
    @elseif (View::hasSection('student-actions'))
      <div class="d-flex align-items-center gap-2">@yield('student-actions')</div>
    @endif
  </div>

  {{-- フラッシュ/エラー（layouts/partials または root/partials どちらにも対応） --}}
  @includeFirst(['layouts.partials.flash','partials.flash'])
  @includeFirst(['layouts.partials.errors','partials.errors'])

  {{-- 子ビュー本体 --}}
  @yield('student-content')
@endsection

@section('content')
    @yield('student-content')
@endsection