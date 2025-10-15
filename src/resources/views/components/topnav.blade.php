@props([
  // 未指定ならログイン中のガードから自動判定
  'role' => null,               // 'admin' | 'teacher' | 'student' | 'guardian' | null=auto
  // 追加メニュー（空なら出さない＝プロフィール/ログアウトのみ）
  'items' => [],                // 例: [['label'=>'ダッシュボード','route'=>'admin.dashboard','icon'=>'gauge-high','active'=>'admin.dashboard']]
  // 見た目
  'skin'  => 'light',           // 'light' | 'dark'
  // 明示したいときだけ指定（未指定なら自動解決）
  'logoutRoute' => null,
])

@php
  use Illuminate\Support\Facades\Route;

  /** ロール自動判定 */
  $detectedRole = auth('admin')->check() ? 'admin'
                : (auth('teacher')->check() ? 'teacher'
                : (auth('student')->check() ? 'student'
                : (auth('guardian')->check() ? 'guardian' : null)));
  $role = $role ?? $detectedRole ?? 'student';

  /** 表示用ラベル */
  $roleLabelMap = [
    'student'  => '学生',
    'guardian' => '保護者',
    'teacher'  => '教員',
    'admin'    => '管理者',
  ];
  $roleLabel = $roleLabelMap[$role] ?? $role;

  /** テーマ */
  $isDark = ($skin === 'dark');

  /** ブランドリンク候補（存在する最初を採用） */
  $brandRoute = collect([$role.'.home', $role.'.dashboard', 'dashboard', $items[0]['route'] ?? null])
                  ->first(fn($r) => $r && Route::has($r)) ?? '#';

  /** プロフィール先の自動解決 */
  $profileRoute = collect([$role.'.profile.show', $role.'.profile', 'profile.show', 'profile'])
                    ->first(fn($r) => $r && Route::has($r));

  /** ログアウト先の自動解決 */
  $logoutRouteName = $logoutRoute ?: ($role ? $role.'.logout' : null);
  if (!$logoutRouteName || !Route::has($logoutRouteName)) {
    foreach (['admin.logout','teacher.logout','student.logout','guardian.logout','logout'] as $cand) {
      if (Route::has($cand)) { $logoutRouteName = $cand; break; }
    }
  }
@endphp

<nav class="navbar navbar-expand-lg {{ $isDark ? 'navbar-dark bg-dark' : 'navbar-light bg-white border-bottom' }} shadow-sm mb-3">
  <div class="container">
    <a class="navbar-brand fw-bold" href="{{ $brandRoute !== '#' ? route($brandRoute) : '#' }}">
      MyCampus
      <small class="ms-2 text-muted" style="font-size:.8rem;">{{ $roleLabel }}</small>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
            aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div id="mainNav" class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        {{-- 追加メニュー（任意） --}}
        @foreach($items as $it)
          @continue(!Route::has($it['route']))
          @php $isActive = request()->routeIs($it['active'] ?? $it['route']); @endphp
          <li class="nav-item">
            <a class="nav-link {{ $isActive ? 'active fw-semibold' : '' }}" href="{{ route($it['route']) }}">
              @isset($it['icon'])
                <i class="fa-solid fa-{{ $it['icon'] }} me-1"></i>
              @endisset
              {{ $it['label'] }}
            </a>
          </li>
        @endforeach

        {{-- 仕切り（追加メニューがあるときだけ） --}}
        @if(!empty($items))
          <li class="nav-item d-none d-lg-block"><span class="nav-link disabled">|</span></li>
        @endif

        {{-- プロフィール --}}
        @if($profileRoute)
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs($profileRoute) ? 'active fw-semibold' : '' }}"
               href="{{ route($profileRoute) }}">
              <i class="fa-regular fa-id-card me-1"></i> プロフィール
            </a>
          </li>
        @endif

        {{-- ログアウト --}}
        @if($logoutRouteName && Route::has($logoutRouteName))
          <li class="nav-item">
            <a class="nav-link text-danger" href="#"
               onclick="event.preventDefault();document.getElementById('logout-form').submit();">
              <i class="fa-solid fa-right-from-bracket me-1"></i> ログアウト
            </a>
            <form id="logout-form" class="d-none" method="POST" action="{{ route($logoutRouteName) }}">
              @csrf
            </form>
          </li>
        @endif
      </ul>
    </div>
  </div>
</nav>

@once
  {{-- Font Awesome（未読込なら） --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endonce