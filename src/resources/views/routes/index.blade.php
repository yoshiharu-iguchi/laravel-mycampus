<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>経路一覧（駅すぱあとAPI）</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <form class="row g-2 mb-4" method="GET" action="{{ route('routes.index') }}">
  <div class="col-12 col-md-3">
    <label class="form-label small mb-1">出発駅</label>
    <input type="text" name="from" class="form-control" value="{{ request('from', '大宮(埼玉県)') }}" placeholder="例）大宮(埼玉県)">
  </div>
  <div class="col-12 col-md-3">
    <label class="form-label small mb-1">到着駅</label>
    <input type="text" name="to" class="form-control" value="{{ request('to', '東京') }}" placeholder="例）東京">
  </div>
  <div class="col-6 col-md-2">
    <label class="form-label small mb-1">日付</label>
    <input type="date" name="date" class="form-control"
      value="{{ request('date', now()->format('Y-m-d')) }}">
  </div>
  <div class="col-6 col-md-2">
    <label class="form-label small mb-1">時刻</label>
    <input type="time" name="time" class="form-control"
      value="{{ request('time', now()->format('H:i')) }}">
  </div>
  <div class="col-12 col-md-2 d-flex align-items-end">
    <button class="btn btn-primary w-100" type="submit">検索</button>
  </div>
</form>

  <h1 class="h5 mb-3">大宮 → 東京（デモ）</h1>

  @if($error)
    <div class="alert alert-danger"><pre class="mb-0">{{ $error }}</pre></div>
  @endif

  @forelse($courses as $c)
    <div class="card mb-3 shadow-sm">
      <div class="card-body d-flex justify-content-between">
        <div>
          <div class="small text-muted">{{ $c['date'] }}</div>
          <h2 class="h6 mb-1">{{ $c['from'] }} → {{ $c['to'] }}</h2>
          <div class="mb-2">
          <strong>{{ $c['dep_time'] }}</strong> 発 /
          <strong>{{ $c['arr_time'] }}</strong> 着
          <span class="badge bg-secondary ms-2">所要 {{ $c['onboard'] }} 分</span>
            <span class="badge bg-secondary ms-1">
    距離
    @if(!is_null($c['distance']))
      {{ number_format($c['distance'], 1) }} km
    @else
      -
    @endif
  </span>
</div>
          <div class="text-muted">
            列車：{{ $c['train_names'] }}
            @if(!empty($c['train_nos']))（{{ $c['train_nos'] }}）@endif
            @if(!empty($c['dest']))／ 行先：{{ $c['dest'] }}@endif
        </div>

        </div>
        <div class="text-end">
          <div class="fs-5 fw-bold">合計 ¥{{ number_format($c['total']) }}</div>
          <div class="small text-muted">
            運賃 ¥{{ number_format($c['fare']) }}
            @if($c['seat']) ＋ {{ $c['seat'] }} ¥{{ number_format($c['seat_fee']) }} @endif
          </div>
          <a class="btn btn-primary btn-sm mt-2" href="#">詳細（ダミー）</a>
        </div>
      </div>
    </div>
  @empty
    <div class="alert alert-warning">経路が見つかりませんでした。</div>
  @endforelse

</div>
</body>
</html>