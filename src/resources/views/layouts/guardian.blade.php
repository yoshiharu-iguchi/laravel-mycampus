@extends('layouts.guardian')

@section('title', trim($__env->yieldContent('title', '保護者メニュー')).' | 保護者')

@section('topnav')
  @php
    // 例：未読お知らせ件数などを badge に
    $unread = isset($unread) ? $unread : null;
  @endphp
  @include('layouts.partials.topnav', [
    'role' => 'guardian',
    'logoutRoute' => 'guardian.logout', // ルート名はプロジェクト側に合わせる
    'items' => [
      ['label'=>'ホーム','route'=>'guardian.home'],
      ['label'=>'お子さま情報','route'=>'guardian.student.show'],
      ['label'=>'実習・面談','route'=>'guardian.internships.index'],
      ['label'=>'お知らせ','route'=>'guardian.notices.index', 'badge'=>$unread],
      ['label'=>'プロフィール','route'=>'guardian.profile.show'],
    ],
  ])
@endsection

@section('content')
  @yield('guardian-content')
@endsection