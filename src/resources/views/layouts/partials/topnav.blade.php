@props([
  'role' => null,          // 'student' | 'guardian' | 'admin' など
  'items' => [],           // [['label'=>'','route'=>'','icon'=>null,'badge'=>null,'can'=>true], ...]
  'logoutRoute' => null,   // 例: 'student.logout'
  'skin' => 'admin',       // 'admin' = dark / 'light' = white
])

@once
  @push('head')
    {{-- Bootstrap Icons（CDN） --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
      /* --- Adminと同様のナビスキン（ダーク）／ライト --- */
      .app-navbar .navbar { transition: background .2s ease; }
      .app-navbar.nav-dark { --nav-bg:#111827; --nav-fg:#fff; --nav-accent:#60a5fa; }
      .app-navbar.nav-light{ --nav-bg:#fff;    --nav-fg:#111827; --nav-accent:#111827; }
      .app-navbar .navbar { background: var(--nav-bg) !important; }
      .app-navbar .navbar-brand,
      .app-navbar .nav-link { color: var(--nav-fg) !important; }
      .app-navbar .nav-link { opacity:.85; display:flex; align-items:center; gap:.45rem; }
      .app-navbar .nav-link:hover { opacity:1; }
      .app-navbar .nav-link .bi { font-size:1.05rem; line-height:1; }
      .app-navbar .nav-link.active { opacity:1; position:relative; }
      .app-navbar .nav-link.active::after{
        content:""; position:absolute; left:0; right:0; bottom:-10px; height:2px;
        background: var(--nav-accent); border-radius:2px;
      }
      .app-navbar .badge { font-weight:600; }
      .app-navbar.nav-light .navbar { border-bottom:1px solid #e5e7eb; }
    </style>
  @endpush
@endonce

@php
  use Illuminate\Support\Facades\Route as R;

  $isDark  = ($skin ?? 'admin') === 'admin';
  $navTone = $isDark ? 'navbar-dark bg-dark' : 'navbar-light bg-white';

  // ルート名 → デフォルトアイコン（必要に応じて拡張）
  $iconMap = [
    // 共通
    'home'                => 'house-door',
    'profile.show'        => 'person-circle',
    // 学生
    'student.home'        => 'house-door',
    'student.tr.create'   => 'geo-alt',
    'student.tr.index'    => 'list-check',
    'student.facilities.index' => 'building',
    'student.profile.show'=> 'person-circle',
    // 保護者（例）
    'guardian.home'       => 'house-door',
    'guardian.notices.index' => 'bell',
    // 管理（例）
    'admin.dashboard'     => 'speedometer2',
    'admin.tr.index'      => 'inboxes',
    'admin.students.index'=> 'people',
  ];

  $brand = match($role){
    'student'  => 'MyCampus / 学生',
    'guardian' => 'MyCampus / 保護者',
    'admin'    => 'MyCampus / 管理',
    default    => 'MyCampus'
  };
@endphp

<div class="app-navbar {{ $isDark ? 'nav-dark' : 'nav-light' }}">
  <nav class="navbar navbar-expand-lg {{ $navTone }} sticky-top">
    <div class="container">
      <a class="navbar-brand fw-bold" href="{{ url('/') }}">{{ $brand }}</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav-{{$role}}">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="topnav-{{$role}}">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          @foreach($items as $it)
            @continue(isset($it['can']) && !$it['can'])

            @php
              $hasRoute = isset($it['route']) && R::has($it['route']); // 未定義ルートはスキップ
              $isActive = $hasRoute ? (isset($it['active']) ? $it['active'] : request()->routeIs($it['route'])) : false;

              // アイコンは items[x]['icon'] 優先、なければルート名で自動割当て
              $routeName = $it['route'] ?? '';
              $autoKey   = $routeName;
              $iconName  = $it['icon'] ?? ($iconMap[$autoKey] ?? ($iconMap[basename($autoKey)] ?? null)); // basenameで末尾キーも検索
            @endphp

            @if($hasRoute)
              <li class="nav-item">
                <a class="nav-link {{ $isActive ? 'active' : '' }}" href="{{ route($it['route']) }}">
                  @if($iconName)<i class="bi bi-{{ $iconName }}" aria-hidden="true"></i>@endif
                  <span>{{ $it['label'] }}</span>
                  @if(isset($it['badge']) && $it['badge'] !== null)
                    <span class="badge text-bg-secondary ms-1">{{ $it['badge'] }}</span>
                  @endif
                </a>
              </li>
            @endif
          @endforeach
        </ul>

        <div class="d-flex align-items-center gap-2">
          @auth($role)
            <span class="small" style="color:var(--nav-fg);opacity:.8;">
              <i class="bi bi-person-check me-1" aria-hidden="true"></i>{{ auth($role)->user()->name ?? 'ログイン中' }}
            </span>
          @endauth

          @if($logoutRoute)
            <form method="POST" action="{{ route($logoutRoute) }}">
              @csrf
              <button class="btn btn-sm {{ $isDark ? 'btn-outline-light' : 'btn-outline-dark' }}">
                <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>ログアウト
              </button>
            </form>
          @endif
        </div>
      </div>
    </div>
  </nav>
</div>
