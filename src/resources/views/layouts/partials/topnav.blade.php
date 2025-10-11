@props([
  'role' => 'student',          // 'student' or 'guardian'（表示文言にだけ使う）
  'items' => [],                // [['label'=>'ホーム','route'=>'student.home','icon'=>'house']]
  'skin'  => 'dark',            // 'dark' | 'light' | 'admin'（=darkと同義）
  'logoutRoute' => 'logout',    // ポスト先（既存の共通 logout でOK）
])

@php
  $isDark = in_array($skin, ['dark','admin'], true);
  $roleLabelMap = [
    'student'  => '学生',
    'guardian' => '保護者',
    'teacher'  => '教員',
    'admin'    => '管理者',
  ];
  $roleLabel = $roleLabelMap[$role] ?? $role;
@endphp

<nav class="navbar navbar-expand-lg {{ $isDark ? 'navbar-dark bg-dark' : 'navbar-light bg-white border-bottom' }}">
  <div class="container">
    <a class="navbar-brand fw-bold" href="{{ route($items[0]['route'] ?? ($role.'.home')) }}">
      MyCampus
      <small class="ms-2 text-muted" style="font-size:.8rem;">{{ $roleLabel }}</small>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
            aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div id="mainNav" class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        @foreach($items as $it)
          @php
            $active = request()->routeIs($it['route']) ? 'active' : '';
          @endphp
          <li class="nav-item">
            <a class="nav-link {{ $active }}" href="{{ route($it['route']) }}">
              @isset($it['icon'])
                <i class="fa-solid fa-{{ $it['icon'] }} me-1"></i>
              @endisset
              {{ $it['label'] }}
            </a>
          </li>
        @endforeach

        {{-- 仕切り --}}
        <li class="nav-item d-none d-lg-block">
          <span class="nav-link disabled">|</span>
        </li>

        {{-- ログアウト --}}
        <li class="nav-item">
          <a class="nav-link text-danger" href="#"
             onclick="event.preventDefault();document.getElementById('logout-form').submit();">
            <i class="fa-solid fa-right-from-bracket me-1"></i> ログアウト
          </a>
          <form id="logout-form" class="d-none" method="POST" action="{{ route($logoutRoute) }}">
            @csrf
          </form>
        </li>
      </ul>
    </div>
  </div>
</nav>

@once
  {{-- Font Awesome（未読込なら） --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endonce
