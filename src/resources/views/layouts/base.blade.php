<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>@yield('title', 'MyCampus')</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap（必要ならCDNでOK。すでに別レイアウトにあるなら共通化） -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body{ background:#f5f5f5; font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans JP", sans-serif; }
    .wrap{ max-width: 980px; margin: 6vh auto; padding: 24px; background:#fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
    .brand{ font-weight:700; letter-spacing:.2px; }
    .subtle{ color:#6b7280; }
    .nav-link.active{ font-weight:600; }
  </style>

  @stack('head')
</head>
<body>

  {{-- ここに役割別ナビが入ります --}}
  @yield('topnav')

  <main class="wrap">
    @includeFirst(['layouts.partials.flash','partials.flash'])

    @yield('content')
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>
</html>